<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = Activity::with(['causer', 'subject'])
            ->latest();

        if ($search = $request->input('search')) {
            $logs->where('description', 'like', "%{$search}%");
        }

        return Inertia::render('Logs/Index', [
            'logs' => $logs->paginate(50)->through(fn ($log) => [
                'id' => $log->id,
                'log_name' => $log->log_name,
                'description' => $log->description,
                'subject_type' => $log->subject_type,
                'subject_id' => $log->subject_id,
                'causer' => $log->causer ? [
                    'id' => $log->causer->id,
                    'username' => $log->causer->username ?? $log->causer->name ?? 'Unknown',
                    'email' => $log->causer->email ?? null,
                ] : null,
                'properties' => $log->properties,
                'created_at' => $log->created_at,
            ]),
            'filters' => $request->only(['search']),
        ]);
    }
    
    public function forModel(string $modelType, int $modelId)
    {
        $modelClass = "App\\Models\\" . $modelType;
        
        if (!class_exists($modelClass)) {
            abort(404, "Model not found");
        }
        
        return Inertia::render('Logs/ModelLogs', [
            'logs' => Activity::forSubject($modelClass::findOrFail($modelId))
                ->with(['causer'])
                ->latest()
                ->paginate(20)
                ->through(fn ($log) => [
                    // ... same transformation as index method
                ]),
            'model' => $modelClass::withTrashed()->find($modelId),
            'modelType' => $modelType,
            'filters' => Request::only(['search'])
        ]);
    }
}