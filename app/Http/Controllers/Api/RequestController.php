<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\RequestDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Account;
use App\Models\Release;
use App\Models\User;
use App\Models\RequestApproval;
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
use App\Notifications\NewRequestNotification;


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
            ->whereIn('status', ['propertyCustodian', 'to_order', 'partially_released'])
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

    public function released()
    {
        $user = Auth::user();

        $requests = Request::with(['department', 'user', 'details'])
            ->where('status', 'released')
            ->where('department_id', $user->department_id)
            ->get();

        return Inertia::render('Request/Released', [
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

    public function show(Request $request)
    {
        $user = Auth::user();

        return Inertia::render('Request/Show', [
            'request' => $request->load([
                'user', 
                'department', 
                'details',
                'approvals.user'
            ]),
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
            $validated['request_date'] = now();

            // Create the request
            $requestModel = Request::create(collect($validated)->except('items')->toArray());
            $requestModel->user->notify(new NewRequestNotification($requestModel));

            $departmentApprover = User::where('department_id', $validated['department_id'])
                ->where('access_id', 3)
                ->where('id', '!=', $validated['user_id']) // Exclude creator if needed
                ->first();

            // Notify department users with access level 3
            if ($departmentApprover) {
                try {
                    $departmentApprover->notify(new NewRequestNotification($requestModel));
                } catch (\Exception $e) {
                    \Log::error("Approver notification failed", [
                        'approver' => $departmentApprover->email,
                        'error' => $e->getMessage()
                    ]);
                }
            } else {
                \Log::warning("No approver found for department {$validated['department_id']}");
            }

            // Log the creation with full details
            $creatorUser = User::find($validated['user_id']);
            $creator = $creatorUser?->username ?? 'system';
            $description = "Request #{$validated['request_no']} was created by {$creator}";

            RequestApproval::create([
                'request_id' => $requestModel->id,
                'user_id' => $validated['user_id'],
                'status' => $validated['status'],
                'remarks' => $description,
                'approved_at' => now(),
            ]);

            activity()
                ->performedOn($requestModel)
                ->causedBy($creatorUser)
                ->useLog('Request Created')
                ->withProperties([
                    'action' => 'create',
                    'event' => 'Request Created',
                    'ip_address' => $request->ip(),
                    'request_data' => $validated, 
                    'items_count' => count($validated['items']),
                    'department' => $requestModel->department->name,
                    'request_no' => $validated['request_no'],
                ])
                ->log($description);

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
        $authUser = auth()->user();

        if (in_array($validated['status'], $passwordRequiredStatuses)) {
            if (!Hash::check($validated['password'], $authUser->password)) {
                return back()->withErrors([
                    'password' => 'Invalid password',
                ]);
            }
        }

        // Store old status for logging
        $oldStatus = $request->status;

        // Determine update data
        $updateData = ['status' => $validated['status']];
        $validated['director_approved_at'] = now();

        $request->update($updateData);

        $description = "Request #{$request->request_no} status changed from {$oldStatus} to {$validated['status']} by {$authUser->username}";

        RequestApproval::create([
            'request_id' => $request->id,
            'user_id' => $authUser->id,
            'status' => $validated['status'],
            'approved_at' => now(),
            'remarks' => $description,
        ]);

        // Log the status change

        activity()
            ->performedOn($request)
            ->causedBy($authUser)
            ->useLog('Status Updated')
            ->withProperties([
                'action' => 'status_update',
                'event' => 'Status Updated',
                'ip_address' => $httpRequest->ip(),
                'old_status' => $oldStatus,
                'new_status' => $validated['status'],
                'changed_by' => $authUser->username,
                'changed_by_role' => $authUser->role,
                'request_no' => $request->request_no,
            ])
            ->log($description);

        return back()->with('success', 'Request status updated successfully');
    }

    public function release(Request $request)
    {
        return Inertia::render('Request/Release/Index', [
            'request' => $request->load([
                'details' => function ($query) {
                    $query->where('quantity', '!=', 0);
                },
                'user', 
                'department'
            ]),
            'departments' => Department::all(),
            'current_user' => [
                'id' => auth()->id(),
            ]
        ]);
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
                'notes' => 'nullable|string',
                'user_id' => 'required|exists:users,id'
            ]);

            $authUser = User::findOrFail($validated['user_id']);

            $release = Release::create([
                'request_id' => $request->id,
                'user_id' => $authUser->id,
                'release_date' => now(),
                'notes' => $validated['notes'] ?? null
            ]);

            $totalQuantity = 0;

            foreach ($validated['items'] as $item) {
                $requestDetail = RequestDetail::where('id', $item['request_detail_id'])
                    ->where('request_id', $request->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $availableQuantity = $requestDetail->quantity;

                if ($item['quantity'] > $availableQuantity) {
                    throw ValidationException::withMessages([
                        'quantity' => [
                            "Cannot release {$item['quantity']} items. Only {$availableQuantity} available."
                        ]
                    ]);
                }

                ReleaseDetail::create([
                    'release_id' => $release->id,
                    'request_detail_id' => $item['request_detail_id'],
                    'quantity' => $item['quantity']
                ]);

                $requestDetail->decrement('quantity', $item['quantity']);
                $requestDetail->increment('released_quantity', $item['quantity']);

                $totalQuantity += $item['quantity'];
            }

            $this->updateRequestStatus($request);

            $description = "Released {$totalQuantity} items for request #{$request->request_no} by {$authUser->username}";

            RequestApproval::create([
                'request_id' => $request->id,
                'user_id' => $authUser->id,
                'status' => 'released',
                'approved_at' => now(),
                'remarks' => $description,
            ]);

            // Minimal activity logging
            activity()
                ->performedOn($release)
                ->causedBy($authUser)
                ->useLog('Released Items')
                ->withProperties([
                    'action' => 'release',
                    'event' => 'Items Released',
                    'ip_address' => $httpRequest->ip(),
                    'request_no' => $request->request_no,
                    'released_quantity' => $totalQuantity,
                ])
                ->log("Released {$totalQuantity} items for request #{$request->request_no}");

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

    protected function updateRequestStatus($request)
    {
        $hasRemaining = RequestDetail::where('request_id', $request->id)
            ->where('quantity', '>', 0)
            ->exists();

        $hasReleased = RequestDetail::where('request_id', $request->id)
            ->where('released_quantity', '>', 0)
            ->exists();

        if (!$hasRemaining && $hasReleased) {
            $status = 'fully_released';
        } elseif ($hasRemaining && $hasReleased) {
            $status = 'partially_released';
        } else {
            $status = $request->status;
        }

        $request->update(['status' => $status]);
    }
}