<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Support\Facades\Request;

class ActivityLogController extends Controller
{
public function index()
{
    return Inertia::render('Logs/Index', [
        'logs' => Activity::query()
            ->with(['causer', 'subject'])
            ->latest()
            ->paginate(15)
            ->through(function ($log) {
                return [
                    'id' => $log->id,
                    'description' => $log->description,
                    'log_name' => $log->log_name,
                    'subject_type' => $log->subject_type,
                    'subject_id' => $log->subject_id,
                    'causer' => $log->causer ? [
                        'username' => $log->causer->username,
                        'email' => $log->causer->email
                    ] : null,
                    'subject' => $log->subject ? [
                        'id' => $log->subject->id,
                        'name' => $log->subject->name ?? null,
                        'title' => $log->subject->title ?? null,
                        'request_no' => $log->subject->request_no ?? null
                    ] : null,
                    'properties' => $log->properties->toArray(),
                    'created_at' => $log->created_at->toISOString()
                ];
            }),
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
                ->get(), // Changed from paginate(20) to get()
            'model' => $modelClass::withTrashed()->find($modelId),
            'modelType' => $modelType,
            'filters' => Request::only(['search'])
        ]);
    }
}