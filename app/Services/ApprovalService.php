<?php

namespace App\Services;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ApprovalService
{
    public function __construct(
        protected ActivityLogger $logger
    ) {}

    public function verifyPassword(string $password, ?User $user = null): void
    {
        $user = $user ?? Auth::user();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => 'The provided password is incorrect.',
            ]);
        }
    }

    public function approve(
        Model $entity,
        string $newStatus,
        string $approvalModelClass,
        string $foreignKey,
        ?Request $request = null,
        ?string $remarks = null,
        ?User $user = null,
    ): Model {
        $user = $user ?? Auth::user();
        $request = $request ?? request();

        $entity->update(['status' => $newStatus]);

        $approvalModelClass::create([
            $foreignKey => $entity->getKey(),
            'user_id' => $user->id,
            'status' => $newStatus,
            'remarks' => $remarks ?? "Approved as {$newStatus}",
            'approved_at' => now(),
        ]);

        ActivityLogger::make($request)
            ->on($entity)
            ->log(class_basename($entity) . " approved as {$newStatus}");

        return $entity->fresh();
    }

    public function reject(
        Model $entity,
        string $newStatus,
        string $approvalModelClass,
        string $foreignKey,
        ?Request $request = null,
        ?string $remarks = null,
        ?User $user = null,
    ): Model {
        $user = $user ?? Auth::user();
        $request = $request ?? request();

        $entity->update(['status' => $newStatus]);

        $approvalModelClass::create([
            $foreignKey => $entity->getKey(),
            'user_id' => $user->id,
            'status' => $newStatus,
            'remarks' => $remarks ?? "Rejected as {$newStatus}",
            'approved_at' => now(),
        ]);

        ActivityLogger::make($request)
            ->on($entity)
            ->log(class_basename($entity) . " rejected as {$newStatus}");

        return $entity->fresh();
    }
}
