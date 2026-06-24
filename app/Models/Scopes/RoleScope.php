<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait RoleScope
{
    public function scopeForRole(Builder $query, string $role, ?int $userId = null, ?int $departmentId = null): Builder
    {
        return match ($role) {
            'audit' => $query->whereNotIn('status', ['draft']),
            'purchasing' => $query->whereHas('user', fn($q) => $q->where('department_id', $departmentId)),
            default => $query,
        };
    }

    public function scopeByDepartment(Builder $query, int $departmentId): Builder
    {
        return $query->whereHas('user', fn($q) => $q->where('department_id', $departmentId));
    }
}
