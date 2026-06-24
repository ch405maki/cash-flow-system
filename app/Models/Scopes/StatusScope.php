<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;

trait StatusScope
{
    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', 'rejected');
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', 'draft');
    }

    public function scopeByStatuses(Builder $query, array $statuses): Builder
    {
        return $query->whereIn('status', $statuses);
    }

    public function scopeExcludingStatuses(Builder $query, array $statuses): Builder
    {
        return $query->whereNotIn('status', $statuses);
    }
}
