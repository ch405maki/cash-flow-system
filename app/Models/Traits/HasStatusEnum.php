<?php

namespace App\Models\Traits;

trait HasStatusEnum
{
    public function isStatus(string ...$statuses): bool
    {
        return in_array($this->status, $statuses);
    }

    public function isNotStatus(string ...$statuses): bool
    {
        return !$this->isStatus(...$statuses);
    }

    public function scopeWhereStatus($query, string ...$statuses)
    {
        return $query->whereIn('status', $statuses);
    }

    public function scopeWhereNotStatus($query, string ...$statuses)
    {
        return $query->whereNotIn('status', $statuses);
    }
}
