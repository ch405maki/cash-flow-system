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
        'logs' => Activity::with(['causer', 'subject'])
            ->latest()
            ->paginate(1) 
            ->withQueryString(),
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