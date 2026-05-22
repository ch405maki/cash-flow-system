<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRequestRequest;
use App\Http\Requests\UpdateRequestItemsRequest;
use App\Http\Requests\ReleaseItemsRequest;
use App\Actions\Requests\GenerateRequestNumber;
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
use App\Services\ActivityLogger;

use App\Helpers\MacAddressHelper;
use App\Services\InventoryApiService;
use Carbon\Carbon;
use Illuminate\Support\Str;

use Inertia\Inertia;


class RequestController extends Controller
{
    public function __construct(
        protected GenerateRequestNumber $generateRequestNumber
    ) {}

    public function store(StoreRequestRequest $request): JsonResponse
    {
        try {
            $macAddress = MacAddressHelper::getClientMac($request);
            $validated = $request->validated();

            $creatorUser = Auth::user();
            $userId = $creatorUser->id;

            $validated['request_no'] = $this->generateRequestNumber->execute();
            $validated['request_date'] = now();
            $validated['user_id'] = $userId;

            $requestModel = Request::create(collect($validated)->except('items')->toArray());

            // Notifications
            $this->notifyRequestCreator($requestModel, $userId);
            $this->notifyRequestApprovers($requestModel, $validated['department_id'], $userId, $creatorUser);

            $description = "Request #{$validated['request_no']} was created by " . ($creatorUser?->username ?? 'system');

            RequestApproval::create([
                'request_id' => $requestModel->id,
                'user_id' => $userId,
                'status' => $validated['status'],
                'remarks' => $description,
                'approved_at' => now(),
            ]);

            ActivityLogger::make($request)
                ->on($requestModel)
                ->by($creatorUser)
                ->withMacAddress()
                ->with([
                    'items_count' => count($validated['items']),
                    'request_no' => $validated['request_no'],
                ])
                ->logName('Request Created')
                ->log($description);

            foreach ($validated['items'] as $item) {
                RequestDetail::create([
                    'request_id' => $requestModel->id,
                    'item_id' => $item['item_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'released_quantity' => 0,
                    'unit' => $item['unit'],
                    'item_description' => $item['item_description'],
                    'tracking_status' => 'pending',
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Request created successfully',
                'data' => $requestModel->load(['department', 'user', 'details']),
                'meta' => [
                    'created_at' => now()->toDateTimeString(),
                    'items_count' => count($validated['items']),
                    'mac_address' => $macAddress,
                ],
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
                'suggestions' => [
                    'available_request_no' => $this->generateRequestNumber->execute(),
                ],
            ], 422);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Server error',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function updateItems(UpdateRequestItemsRequest $httpRequest, Request $request)
    {
        DB::beginTransaction();
        try {
            $validated = $httpRequest->validated();

            // Delete existing details
            $deletedCount = $request->details()->delete();
            \Log::debug("Deleted {$deletedCount} existing items");

            // Create new details
            $createdItems = [];
            foreach ($validated['details'] as $detail) {
                $createdItem = $request->details()->create([
                    'request_id' => $request->id,
                    'item_id' => $detail['item_id'] ?? null,
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

    public function releaseItems(ReleaseItemsRequest $httpRequest, Request $request, InventoryApiService $inventoryApi)
    {
        DB::beginTransaction();

        try {
            $validated = $httpRequest->validated();

            $authUser = User::findOrFail($validated['user_id']);

            $signatureData = $validated['signature'] ?? null;

            $release = Release::create([
                'request_id'      => $request->id,
                'user_id'         => $authUser->id,
                'release_date'    => now(),
                'notes'           => $validated['notes'] ?? null,
                'signature_image' => $signatureData['image'] ?? null,
                'signed_by'       => $signatureData['signer_name'] ?? null,
                'signed_at'       => $signatureData['signed_at'] ? \Carbon\Carbon::parse($signatureData['signed_at']) : null,
            ]);

            $totalQuantity    = 0;
            $inventorySynced  = 0;
            $inventorySkipped = 0;

            foreach ($validated['items'] as $item) {

                $requestDetail = RequestDetail::where('id', $item['request_detail_id'])
                    ->where('request_id', $request->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $remainingQuantity = $requestDetail->quantity - $requestDetail->released_quantity;

                if ($item['quantity'] > $remainingQuantity) {
                    throw ValidationException::withMessages([
                        'quantity' => [
                            "Cannot release {$item['quantity']} items. Only {$remainingQuantity} available."
                        ]
                    ]);
                }

                /*
                |--------------------------------------------------------------------------
                | Inventory API — only if item is linked
                |--------------------------------------------------------------------------
                */
                if ($requestDetail->item_id) {
                    $inventoryData = [
                        'item_id'            => $requestDetail->item_id,
                        'user_id'            => 1,
                        'type'               => 'Out',
                        'quantity'           => $item['quantity'],
                        'source_destination' => $request->department->department_name ?? 'N/A',
                        'personnel_name'     => trim(
                            ($request->user->first_name ?? '') . ' ' .
                            ($request->user->last_name ?? '')
                        ),
                        'reference_no'       => 'REL-' . now()->format('YmdHis') . '-' . $requestDetail->id,
                        'note'               => $request->purpose ?? 'N/A'
                    ];

                    \Log::info('Sending to inventory API:', $inventoryData);

                    $inventoryResult = $inventoryApi->createTransaction($inventoryData);

                    \Log::info('Inventory API response:', $inventoryResult);

                    if (!$inventoryResult['success']) {
                        throw new \Exception(
                            "Inventory update failed for '{$requestDetail->item_description}': " .
                            ($inventoryResult['error'] ?? 'Unknown error')
                        );
                    }

                    $inventorySynced++;

                } else {
                    // Not linked to inventory — release proceeds, sync skipped
                    \Log::info("Item '{$requestDetail->item_description}' (detail #{$requestDetail->id}) has no inventory link. Skipping inventory sync.");
                    $inventorySkipped++;
                }

                /*
                |--------------------------------------------------------------------------
                | Save Release Detail
                |--------------------------------------------------------------------------
                */
                ReleaseDetail::create([
                    'release_id'        => $release->id,
                    'request_detail_id' => $item['request_detail_id'],
                    'quantity'          => $item['quantity']
                ]);

                /*
                |--------------------------------------------------------------------------
                | Update Request Detail
                |--------------------------------------------------------------------------
                */
                $requestDetail->increment('released_quantity', $item['quantity']);
                $requestDetail->refresh();

                $requestDetail->tracking_status =
                    $requestDetail->released_quantity >= $requestDetail->quantity
                        ? 'completed'
                        : 'partial';

                $requestDetail->save();

                $totalQuantity += $item['quantity'];
            }

            /*
            |--------------------------------------------------------------------------
            | Update Request Status
            |--------------------------------------------------------------------------
            */
            $this->updateRequestStatus($request);

            $description = "Released {$totalQuantity} items for request #{$request->request_no} by {$authUser->username}";

            if ($inventorySkipped > 0) {
                $description .= " ({$inventorySkipped} item(s) not linked to inventory, skipped sync)";
            }

            RequestApproval::create([
                'request_id'  => $request->id,
                'user_id'     => $authUser->id,
                'status'      => 'released',
                'approved_at' => now(),
                'remarks'     => $description,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Commit FIRST
            |--------------------------------------------------------------------------
            */
            DB::commit();

            /*
            |--------------------------------------------------------------------------
            | Activity Log AFTER Commit
            |--------------------------------------------------------------------------
            */
            ActivityLogger::make($httpRequest)
                ->on($release)
                ->by($authUser)
                ->withMacAddress()
                ->with([
                    'request_no'        => $request->request_no,
                    'released_quantity' => $totalQuantity,
                    'inventory_synced'  => $inventorySynced,
                    'inventory_skipped' => $inventorySkipped,
                ])
                ->logName('Released Items')
                ->log("Released {$totalQuantity} items for request #{$request->request_no}. Inventory synced: {$inventorySynced}, skipped (unlinked): {$inventorySkipped}");

            return response()->json([
                'success' => true,
                'message' => $inventorySkipped > 0
                    ? "Items released successfully. {$inventorySkipped} item(s) were not synced to inventory (no link)."
                    : 'Items released successfully and inventory updated',
                'data' => [
                    'release'            => $release->load('details'),
                    'request'            => $request->load('details'),
                    'inventory_synced'   => $inventorySynced,
                    'inventory_skipped'  => $inventorySkipped,
                ]
            ]);

        } catch (ValidationException $e) {

            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors'  => $e->errors()
            ], 422);

        } catch (\Exception $e) {

            DB::rollBack();

            \Log::error('Release failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Release failed: ' . $e->getMessage(),
                'error'   => config('app.debug') ? $e->getMessage() : null
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
            ActivityLogger::make()
                ->on($request)
                ->with([
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'total_quantity' => $details->sum('quantity'),
                    'total_released' => $details->sum('released_quantity'),
                ])
                ->logName('Request Status Update')
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

    protected function notifyRequestCreator($requestModel, int $userId): void
    {
        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => 'Request',
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $userId,
            'data' => json_encode([
                'request_id' => $requestModel->id,
                'title' => 'Request',
                'request_no' => $requestModel->request_no,
                'status' => $requestModel->status,
                'message' => "Request #{$requestModel->request_no} has been created",
                'link' => route('request.show', $requestModel->id),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function notifyRequestApprovers($requestModel, int $departmentId, int $userId, ?User $creatorUser): void
    {
        $approvers = User::where('department_id', $departmentId)
            ->where('access_id', 3)
            ->where('id', '!=', $userId)
            ->get();

        foreach ($approvers as $approver) {
            DB::table('notifications')->insert([
                'id' => (string) Str::uuid(),
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