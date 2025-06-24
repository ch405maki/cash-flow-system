<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\RequestDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Account;
use App\Models\Release;
use App\Models\ReleaseDetail;
use Illuminate\Http\JsonResponse;
use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;


use Inertia\Inertia;


class RequestController extends Controller
{

    public function index()
    {
        $user = Auth::user();

        if (in_array($user->role, ['admin', 'executive_director', 'property_custodian'])) {
            // Admin-level users: See all non-pending requests
            $requests = Request::with(['department', 'user', 'details'])
                ->whereIn('status', ['propertyCustodian', 'partially_released', 'to_order'])
                ->get();
        } else {
            $requests = Request::with(['department', 'user', 'details'])
                ->where('status', 'pending')
                ->where('department_id', $user->department_id)
                ->get();
        }

        return Inertia::render('Request/Index', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }

    public function toReceive()
    {
        $user = Auth::user();

        $requests = Request::with(['department', 'user', 'details'])
            ->whereIn('status', ['propertyCustodian', 'to_order'])
            ->where('department_id', $user->department_id)
            ->get();

        return Inertia::render('Request/ToReceive', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }


    public function rejected()
    {
        $user = Auth::user();

        $requests = Request::with(['department', 'user', 'details'])
            ->where('status', 'rejected')
            ->where('department_id', $user->department_id)
            ->get();

        return Inertia::render('Request/Rejected', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }

    public function show(request $request)
    {
        $user = Auth::user();

        return Inertia::render('Request/Show', [
            'request' => $request->load(['user', 'department', 'details']),
            'accounts' => Account::all(['id', 'account_title']),
            'user' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }

    public function create()
    {
        $requests = Request::with(['department', 'user', 'details'])->get();

        return Inertia::render('Request/Create', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => Auth::id(),
                'department_id' => Auth::user()->department_id,
            ],
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
        $prefix = 'REQ-' . now()->format('Ym') . '-';
        $lastRequest = Request::where('request_no', 'like', $prefix . '%')->latest()->first();
        
        $sequence = $lastRequest 
            ? (int) str_replace($prefix, '', $lastRequest->request_no) + 1
            : 1;
        
        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
    
    public function edit(Request $request)
    {
        return Inertia::render('Request/Edit', [
            'request' => $request->load(['details','user', 'department']),
            'departments' => Department::all(),
        ]);
    }

    public function release(Request $request)
    {
        return Inertia::render('Request/Release', [
            'request' => $request->load(['details','user', 'department']),
            'departments' => Department::all(),
        ]);
    }

    public function updateItems(HttpRequest $httpRequest, Request $request)
    {
        DB::beginTransaction();
        try {
            \Log::debug('Incoming update items request:', $httpRequest->all());

            $validated = $httpRequest->validate([
                'details' => 'required|array|min:1',
                'details.*.quantity' => 'required|numeric|min:1',
                'details.*.unit' => 'required|string|max:20',
                'details.*.item_description' => 'required|string|max:255',
            ]);

            \Log::debug('Validated data:', $validated);

            // Delete existing details
            $deletedCount = $request->details()->delete();
            \Log::debug("Deleted {$deletedCount} existing items");

            // Create new details
            $createdItems = [];
            foreach ($validated['details'] as $detail) {
                $createdItem = $request->details()->create([
                    'request_id' => $request->id,
                    'quantity' => $detail['quantity'],
                    'unit' => $detail['unit'],
                    'item_description' => $detail['item_description']
                ]);
                $createdItems[] = $createdItem->id;
            }

            \Log::debug('Created items with IDs:', $createdItems);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Request items updated successfully',
                'data' => $request->fresh()->load('details'),
                'meta' => [
                    'deleted_count' => $deletedCount,
                    'created_count' => count($createdItems)
                ]
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Validation failed:', $e->errors());
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Update failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update items',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    public function updateStatus(HttpRequest $httpRequest, Request $request)
    {
        $validated = $httpRequest->validate([
            'status' => 'required|in:approved,rejected,propertyCustodian,to_order,released',
            'password' => 'required_if:status,approved,propertyCustodian,to_order,released',
        ]);

        // Password verification for sensitive status changes
        $passwordRequiredStatuses = ['approved', 'propertyCustodian', 'to_order', 'released'];

        if (in_array($validated['status'], $passwordRequiredStatuses)) {
            if (!Hash::check($validated['password'], auth()->user()->password)) {
                return back()->withErrors([
                    'password' => 'Invalid password',
                ]);
            }
        }

        // Determine update data
        $updateData = ['status' => $validated['status']];

        // Update user_id only if user is NOT a property custodian
        if (auth()->user()->role !== 'property_custodian') {
            $updateData['user_id'] = auth()->id();
        }

        $request->update($updateData);

        return back()->with('success', 'Request status updated successfully');
    }


    public function releaseItems(HttpRequest $httpRequest, Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $httpRequest->validate([
                'items' => 'required|array|min:1',
                'items.*.request_detail_id' => [
                    'required',
                    Rule::exists('request_details', 'id')->where('request_id', $request->id)
                ],
                'items.*.quantity' => 'required|integer|min:1',
                'notes' => 'nullable|string'
            ]);

            $release = Release::create([
                'request_id' => $request->id,
                'user_id' => auth()->id() ?? 1,
                'release_date' => now(),
                'notes' => $validated['notes'] ?? null
            ]);

            foreach ($validated['items'] as $item) {
                $requestDetail = RequestDetail::where('id', $item['request_detail_id'])
                    ->where('request_id', $request->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Check if requested quantity is available
                if ($item['quantity'] > $requestDetail->quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => [
                            "Cannot release {$item['quantity']} items. Only {$requestDetail->quantity} available."
                        ]
                    ]);
                }

                // Create release record
                ReleaseDetail::create([
                    'release_id' => $release->id,
                    'request_detail_id' => $item['request_detail_id'],
                    'quantity' => $item['quantity']
                ]);

                // Subtract the released quantity directly from the main quantity
                $requestDetail->decrement('quantity', $item['quantity']);
                $requestDetail->increment('released_quantity', $item['quantity']);
            }

            // Update request status based on remaining quantities
            $fullyReleased = !RequestDetail::where('request_id', $request->id)
                ->where('quantity', '>', 0)
                ->exists();

            $request->update(['status' => $fullyReleased ? 'fully_released' : 'partially_released']);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Items released successfully',
                'data' => [
                    'release' => $release->load('details'),
                    'request' => $request->load('details')
                ]
            ]);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Release failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Release failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}