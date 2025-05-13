<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Request;
use App\Models\RequestDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;

use Inertia\Inertia;

class RequestController extends Controller
{
    public function index()
    {
        $requests = Request::with(['department', 'user', 'details'])->get();

        return Inertia::render('Request/Index', [
            'requests' => $requests,
        ]);
    }

    public function store(HttpRequest $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'request_date' => 'required|date',
                'purpose' => 'required|string|max:500',
                'status' => 'required|in:pending,approved,rejected',
                'department_id' => 'required|exists:departments,id',
                'user_id' => 'required|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit' => 'required|string|max:20',
                'items.*.item_description' => 'required|string|max:255'
            ]);

            // Auto-generate request number
            $validated['request_no'] = $this->generateRequestNumber();

            // Create the request
            $requestModel = Request::create(collect($validated)->except('items')->toArray());

            // Create request details
            foreach ($validated['items'] as $item) {
                RequestDetail::create([
                    'request_id' => $requestModel->id,
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'],
                    'item_description' => $item['item_description']
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Request created successfully',
                'data' => $requestModel->load(['department', 'user', 'details']),
                'meta' => [
                    'created_at' => now()->toDateTimeString(),
                    'items_count' => count($validated['items'])
                ]
            ], 201);

        } catch (ValidationException $e) {
    $errors = $e->errors();
    
    if (isset($errors['request_no'])) {
        $errors['request_no'] = [
            'The request number must be unique.',
            'Suggested available number: ' . $this->generateRequestNumber()
        ];
    }
    
    return response()->json([
        'success' => false,
        'message' => 'Validation error',
        'errors' => $errors,
        'suggestions' => [
            'available_request_no' => $this->generateRequestNumber()
        ]
    ], 422);
}
catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    protected function generateRequestNumber(): string
    {
        $prefix = 'REQ-' . now()->format('Ymd') . '-';
        $lastRequest = Request::where('request_no', 'like', $prefix . '%')->latest()->first();
        
        $sequence = $lastRequest 
            ? (int) str_replace($prefix, '', $lastRequest->request_no) + 1
            : 1;
        
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}