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
                ->when(Request::input('search'), function ($query, $search) {
                    $query->where('description', 'like', "%{$search}%")
                        ->orWhereHas('causer', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                        });
                })
                ->latest()
                ->get(), // Changed from paginate(20) to get()
            'filters' => Request::only(['search']),
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