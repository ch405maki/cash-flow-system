<?php

namespace App\Actions\Approvals;

use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

class ApproveEntity
{
    public function execute(
        Model $entity,
        string $newStatus,
        string $approvalModelClass,
        string $foreignKey,
        ?User $user = null,
        ?string $remarks = null,
    ): Model {
        $user = $user ?? auth()->user();

        $entity->update(['status' => $newStatus]);

        $approvalModelClass::create([
            $foreignKey => $entity->getKey(),
            'user_id' => $user->id,
            'status' => $newStatus,
            'remarks' => $remarks ?? "Approved as {$newStatus}",
            'approved_at' => now(),
        ]);

        ActivityLogger::make()
            ->on($entity)
            ->log(class_basename($entity) . " approved as {$newStatus}");

        return $entity->fresh();
    }
}
