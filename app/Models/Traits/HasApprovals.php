<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasApprovals
{
    public function hasApprovalBy(int $userId): bool
    {
        return $this->approvals()->where('user_id', $userId)->exists();
    }

    public function latestApproval()
    {
        return $this->approvals()->latest('approved_at')->first();
    }

    public function approvalByRole(string $role)
    {
        return $this->approvals()->where('role', $role)->first();
    }

    public function approvalsByStatus(string $status)
    {
        return $this->approvals()->where('status', $status)->get();
    }

    public function scopeWhereHasApprovalBy($query, int $userId)
    {
        return $query->whereHas('approvals', fn($q) => $q->where('user_id', $userId));
    }

    public function scopeWhereDoesntHaveApprovalBy($query, int $userId)
    {
        return $query->whereDoesntHave('approvals', fn($q) => $q->where('user_id', $userId));
    }
}
