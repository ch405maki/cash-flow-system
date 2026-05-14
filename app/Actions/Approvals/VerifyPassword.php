<?php

namespace App\Actions\Approvals;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class VerifyPassword
{
    public function execute(string $password, ?User $user = null): void
    {
        $user = $user ?? auth()->user();

        if (!$user || !Hash::check($password, $user->password)) {
            throw ValidationException::withMessages([
                'password' => ['The provided password is incorrect.'],
            ]);
        }
    }
}
