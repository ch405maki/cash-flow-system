<?php

namespace App\Models\Traits;

use App\Services\ActivityLogger;
use Illuminate\Http\Request;

trait HasActivityLogging
{
    public function logActivity(?Request $request = null): ActivityLogger
    {
        return ActivityLogger::make($request)->on($this);
    }

    public function logCreation(?Request $request = null, array $extra = []): void
    {
        ActivityLogger::make($request)->created($this, $extra);
    }

    public function logStatusChange(string $oldStatus, string $newStatus, ?Request $request = null, array $extra = []): void
    {
        ActivityLogger::make($request)->statusChanged($this, $oldStatus, $newStatus, $extra);
    }
}
