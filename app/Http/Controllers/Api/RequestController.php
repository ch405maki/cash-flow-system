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
use App\Helpers\MacAddressHelper;

use App\Services\InventoryApiService;


use Inertia\Inertia;


class RequestController extends Controller
{

    public function store(HttpRequest $request): JsonResponse
    {
        try {
            $macAddress = MacAddressHelper::getClientMac($request);

            $validated = $request->validate([
                'purpose' => 'required|string|max:500',
                'status' => 'required|in:pending,approved,rejected',
                'department_id' => 'required|exists:departments,id',
                'user_id' => 'required|exists:users,id',
                'items' => 'required|array|min:1',
                'items.*.item_id' => 'nullable|integer', 
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit' => 'required|string|max:20',
                'items.*.item_description' => 'required|string|max:255'
            ]);

            // Auto-generate request number
            $validated['request_no'] = $this->generateRequestNumber();
            $validated['request_date'] = now();

            // Create the request
            $requestModel = Request::create(collect($validated)->except('items')->toArray());
            
            // ===== NOTIFICATION CHANGES START =====
            // Instead of using the notification class, manually insert into notifications table
            
            // 1. Notify the creator
            DB::table('notifications')->insert([
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'type' => 'Request',
                'notifiable_type' => 'App\Models\User',
                'notifiable_id' => $validated['user_id'],
                'data' => json_encode([
                    'request_id' => $requestModel->id,
                    'title' => "Request",
                    'request_no' => $requestModel->request_no,
                    'status' => $requestModel->status,
                    'message' => "Request #{$requestModel->request_no} has been created",
                    'link' => route('request.show', $requestModel->id),
                ]),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 2. Notify department approvers (access level 3)
            $approvers = User::where('department_id', $validated['department_id'])
                ->where('access_id', 3)
                ->where('id', '!=', $validated['user_id'])
                ->get();

            foreach ($approvers as $approver) {
                DB::table('notifications')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'App\Notifications\NewRequestNotification',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $approver->id,
                    'data' => json_encode([
                        'request_id' => $requestModel->id,
                        'request_no' => $requestModel->request_no,
                        'status' => $requestModel->status,
                        'message' => "New request #{$requestModel->request_no} needs approval from " . ($creatorUser->name ?? 'Unknown'),
                        'link' => route('request.show', $requestModel->id),
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            // ===== NOTIFICATION CHANGES END =====

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
                    'mac_address' => $macAddress,
                    'user_agent' => $request->userAgent(),
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
                    'item_id' => $item['item_id'] ?? null,   
                    'quantity' => $item['quantity'],
                    'released_quantity' => 0,
                    'unit' => $item['unit'],
                    'item_description' => $item['item_description'],
                    'tracking_status' => 'pending'
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Request created successfully',
                'data' => $requestModel->load(['department', 'user', 'details']),
                'meta' => [
                    'created_at' => now()->toDateTimeString(),
                    'items_count' => count($validated['items']),
                    'mac_address' => $macAddress
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
        } catch (QueryException $e) {
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

    public function updatePurpose(HttpRequest $httpRequest, $id)
    {
        try {
            $requestModel = Request::findOrFail($id);

            $validated = $httpRequest->validate([
                'purpose' => 'required|string|max:1000',
            ]);

            $requestModel->update([
                'purpose' => $validated['purpose']
            ]);

            return response()->json([
                'message' => 'Purpose updated successfully!',
                'request' => $requestModel->fresh()
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update purpose',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function releaseItems(HttpRequest $httpRequest, Request $request, InventoryApiService $inventoryApi)
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

                // Calculate remaining quantity
                $remainingQuantity = $requestDetail->quantity - $requestDetail->released_quantity;

                if ($item['quantity'] > $remainingQuantity) {
                    throw ValidationException::withMessages([
                        'quantity' => [
                            "Cannot release {$item['quantity']} items. Only {$remainingQuantity} available."
                        ]
                    ]);
                }

                // CHECK IF ITEM HAS INVENTORY ID
                if (!$requestDetail->item_id) {
                    throw ValidationException::withMessages([
                        'item' => [
                            "Item '{$requestDetail->item_description}' is not linked to inventory. Cannot release."
                        ]
                    ]);
                }

                // CALL INVENTORY API FOR DEDUCTION
                $inventoryData = [
                    'item_id' => $requestDetail->item_id,
                    'user_id' => 1, // Hardcoded as requested
                    'type' => 'Out',
                    'quantity' => $item['quantity'],
                    'source_destination' => $request->department->department_name ?? 'N/A',
                    'personnel_name' => trim(($request->user->first_name ?? '') . ' ' . ($request->user->last_name ?? '')),
                    'reference_no' => 'REL-' . now()->format('Ymd'), // Auto-generate
                    'note' => $request->purpose ?? 'N/A'
                ];
                
                // Use the service to create transaction
                $inventoryResult = $inventoryApi->createTransaction($inventoryData);
                
                if (!$inventoryResult['success']) {
                    throw new \Exception("Inventory update failed: " . $inventoryResult['error']);
                }

                ReleaseDetail::create([
                    'release_id' => $release->id,
                    'request_detail_id' => $item['request_detail_id'],
                    'quantity' => $item['quantity']
                ]);

                // Update request detail
                $requestDetail->increment('released_quantity', $item['quantity']);
                
                // Update tracking status
                $newReleasedTotal = $requestDetail->released_quantity;
                if ($newReleasedTotal >= $requestDetail->quantity) {
                    $requestDetail->tracking_status = 'completed';
                } else {
                    $requestDetail->tracking_status = 'partial';
                }
                $requestDetail->save();

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

            // Activity logging
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
                    'inventory_synced' => true
                ])
                ->log("Released {$totalQuantity} items for request #{$request->request_no} and synced with inventory");

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Items released successfully and inventory updated',
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
                'message' => 'Release failed: ' . $e->getMessage(),
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Update request status based on released quantities
     */
    protected function updateRequestStatus(Request $request)
    {
        // Refresh to get latest data
        $request->refresh();
        $request->load('details');
        
        $details = $request->details;
        
        if ($details->isEmpty()) {
            return;
        }
        
        $statusChanged = false;
        $newStatus = $request->status;
        
        // Check if all items are fully released
        $allItemsFullyReleased = $details->every(function ($detail) {
            return $detail->released_quantity >= $detail->quantity;
        });
        
        // Check if any items have been released
        $anyItemsReleased = $details->some(function ($detail) {
            return $detail->released_quantity > 0;
        });
        
        // Determine new status
        if ($allItemsFullyReleased && $anyItemsReleased) {
            $newStatus = 'released';
            $statusChanged = true;
        } elseif ($anyItemsReleased && !$allItemsFullyReleased) {
            if ($request->status !== 'partially_released') {
                $newStatus = 'partially_released';
                $statusChanged = true;
            }
        }
        
        // Update if status changed
        if ($statusChanged) {
            $oldStatus = $request->status;
            $request->update(['status' => $newStatus]);
            
            // Log the status change
            activity()
                ->performedOn($request)
                ->causedBy(auth()->user())
                ->useLog('Request Status Update')
                ->withProperties([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'total_quantity' => $details->sum('quantity'),
                    'total_released' => $details->sum('released_quantity')
                ])
                ->log("Request status automatically updated from {$oldStatus} to {$newStatus}");
        }
    }

    // Add to RequestController
    public function checkInventoryAvailability(Request $request)
    {
        try {
            $request->load('details');
            $inventoryApi = app(InventoryApiService::class);
            
            $availability = [];
            
            foreach ($request->details as $detail) {
                if ($detail->item_id) {
                    $check = $inventoryApi->checkProductAvailability(
                        $detail->item_id,
                        $detail->quantity - $detail->released_quantity
                    );
                    
                    $availability[$detail->id] = [
                        'item_description' => $detail->item_description,
                        'requested' => $detail->quantity - $detail->released_quantity,
                        'available' => $check['success'] ? $check['available'] : null,
                        'has_stock' => $check['success'] ? $check['has_stock'] : false,
                        'error' => $check['error'] ?? null
                    ];
                }
            }
            
            return response()->json([
                'success' => true,
                'availability' => $availability
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check inventory',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Api method to update tagging status
    public function data(): JsonResponse
    {
        $requests = Request::with(['department', 'user', 'details'])
            ->whereIn('status', ['propertyCustodian'])
            ->get();

        return response()->json([
            'success' => true,
            'data' => $requests,
        ]);
    }
}