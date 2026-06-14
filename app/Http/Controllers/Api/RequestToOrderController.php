<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\Request;
use App\Models\RequestDetail;
use App\Models\RequestToOrder;
use App\Models\RequestToOrderApproval;
use App\Models\RequestToOrderRelease;
use App\Models\RequestToOrderDetail;
use App\Models\User;
use App\Models\Unit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\ActivityLogger;
use Illuminate\Support\Facades\Log;

class RequestToOrderController extends Controller
{
    public function index(HttpRequest $request): JsonResponse
    {
        $user = Auth::user();
        $pageType = $request->query('pageType', 'index');

        $requests = match ($pageType) {
            'pending' => RequestToOrder::with('details')
                ->whereIn('status', ['pending'])
                ->get(),
            'for-approval' => RequestToOrder::with('details')
                ->whereIn('status', ['forEOD'])
                ->get(),
            'on-process' => RequestToOrder::with('details')
                ->where('status', 'forPO')
                ->get(),
            'approved' => RequestToOrder::with('details')
                ->where('status', 'forPO')
                ->get(),
            default => RequestToOrder::with('details')
                ->whereIn('status', ['pending'])
                ->get(),
        };

        $forOrders = $pageType === 'index'
            ? Request::with(['department', 'user', 'details'])->whereIn('status', ['to_order'])->get()
            : [];

        return response()->json([
            'success' => true,
            'data' => [
                'requests' => $requests,
                'forOrders' => $forOrders,
                'authUser' => [
                    'id' => $user->id,
                    'role' => $user->role,
                    'department_id' => $user->department_id,
                ],
            ],
        ]);
    }

    public function createData(): JsonResponse
    {
        $requests = Request::with(['details', 'department', 'user'])
            ->where('status', 'to_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'requests' => $requests,
            ],
        ]);
    }

    public function listData(): JsonResponse
    {
        $requests = Request::with([
                'details' => function ($query) {
                    $query->where('quantity', '!=', 0)
                        ->where(function($q) {
                            $q->whereNull('tagging')
                                ->orWhere('tagging', '!=', 'forPurchase');
                        });
                },
                'department',
                'user'
            ])
            ->where('status', 'to_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'requests' => $requests,
            ],
        ]);
    }

    public function showData($id): JsonResponse
    {
        $user = Auth::user();

        $requestOrder = RequestToOrder::with([
            'details.requestDetail.request.department',
            'details.releases.releasedBy',
            'details.request.department',
            'approvals.user',
        ])->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'requestOrder' => $requestOrder,
                'authUser' => [
                    'id' => $user->id,
                    'role' => $user->role,
                    'access' => $user->access_id,
                ],
            ],
        ]);
    }

    public function store(HttpRequest $request): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.item_description' => 'nullable|string|max:255',
            'items.*.detail_id' => 'required|exists:request_details,id',
        ]);

        try {
            DB::transaction(function () use ($validated, $request) {
                $authUser = auth()->user();

                $order = RequestToOrder::create([
                    'user_id' => $authUser->id,
                    'order_no' => $this->generateOrderNumber(),
                    'order_date' => now(),
                    'notes' => $validated['notes'],
                    'status' => 'pending',
                ]);

                foreach ($validated['items'] as $item) {
                    $unit = $item['unit'] ?? null;
                    if ($unit) {
                        $unit = Unit::firstOrCreate(['name' => trim(strtolower($unit))])->name;
                    }
                    $order->details()->create([
                        'quantity' => $item['quantity'],
                        'unit' => $unit,
                        'item_description' => $item['item_description'] ?? null,
                        'request_detail_id' => $item['detail_id'],
                    ]);

                    RequestDetail::where('id', $item['detail_id'])
                        ->update([
                            'tagging' => 'forPurchase',
                            'tracking_status' => 'ordered',
                        ]);
                }

                RequestToOrderApproval::create([
                    'request_to_order_id' => $order->id,
                    'user_id' => $authUser->id,
                    'status' => 'pending',
                    'approved_at' => now(),
                    'remarks' => $validated['notes'],
                ]);

                ActivityLogger::make($request)
                    ->on($order)
                    ->by($authUser)
                    ->with([
                        'order_no' => $order->order_no,
                        'notes' => $order->notes,
                        'items_count' => count($validated['items']),
                    ])
                    ->logName('RequestToOrder')
                    ->log("Request to Order #{$order->order_no} created by {$authUser->username}");
            });

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('RequestToOrder store failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function storeManual(HttpRequest $request): JsonResponse
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.item_description' => 'nullable|string|max:255',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $authUser = auth()->user();

                $order = RequestToOrder::create([
                    'user_id' => $authUser->id,
                    'order_no' => $this->generateOrderNumber(),
                    'order_date' => now(),
                    'notes' => $validated['notes'],
                    'status' => 'pending',
                ]);

                foreach ($validated['items'] as $item) {
                    $unit = $item['unit'] ?? null;
                    if ($unit) {
                        $unit = Unit::firstOrCreate(['name' => trim(strtolower($unit))])->name;
                    }
                    $order->details()->create([
                        'quantity' => $item['quantity'],
                        'unit' => $unit,
                        'item_description' => $item['item_description'] ?? null,
                    ]);
                }

                RequestToOrderApproval::create([
                    'request_to_order_id' => $order->id,
                    'user_id' => $authUser->id,
                    'status' => 'pending',
                    'approved_at' => now(),
                    'remarks' => $validated['notes'],
                ]);

                ActivityLogger::make()
                    ->on($order)
                    ->by($authUser)
                    ->with([
                        'order_no' => $order->order_no,
                        'notes' => $order->notes,
                        'items_count' => count($validated['items']),
                    ])
                    ->logName('RequestToOrder')
                    ->log("Manual Request to Order #{$order->order_no} created by {$authUser->username}");
            });

            return response()->json([
                'success' => true,
                'message' => 'Order created successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('RequestToOrder storeManual failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to create order',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    public function approve($id, HttpRequest $request): JsonResponse
    {
        $request->validate(['password' => 'required']);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password',
                'errors' => ['password' => 'Incorrect password'],
            ], 422);
        }

        $order = RequestToOrder::findOrFail($id);

        if ($order->status === 'forPO') {
            return response()->json([
                'success' => false,
                'message' => 'Already approved',
                'errors' => ['status' => 'Already approved'],
            ], 422);
        }

        DB::transaction(function () use ($order, $user, $request) {
            $order->update(['status' => 'forPO']);

            RequestToOrderApproval::create([
                'request_to_order_id' => $order->id,
                'user_id' => $user->id,
                'status' => 'approved',
                'remarks' => 'Approved by Executive Director',
                'approved_at' => now(),
            ]);

            $creator = User::find($order->user_id);

            if ($creator) {
                DB::table('notifications')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'OrderApproved',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $creator->id,
                    'data' => json_encode([
                        'order_id' => $order->id,
                        'title' => 'Order Approved',
                        'order_no' => $order->order_no,
                        'amount' => $order->total_amount ?? 0,
                        'department' => $order->department->name ?? 'N/A',
                        'purpose' => $order->purpose,
                        'status' => 'forPO',
                        'message' => "Your order #{$order->order_no} has been approved by the Executive Director and is now ready for purchasing",
                        'link' => route('request-to-order.show', $order->id),
                        'approved_by' => $user->name,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $purchasingUsers = User::where('role', 'purchasing')->get();

            foreach ($purchasingUsers as $purchasing) {
                DB::table('notifications')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'OrderReadyForPurchase',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $purchasing->id,
                    'data' => json_encode([
                        'order_id' => $order->id,
                        'title' => 'Order Ready for Purchasing',
                        'order_no' => $order->order_no,
                        'amount' => $order->total_amount ?? 0,
                        'created_by' => $creator?->name ?? 'Unknown',
                        'department' => $order->department->name ?? 'N/A',
                        'purpose' => $order->purpose,
                        'status' => 'forPO',
                        'message' => "Order #{$order->order_no} has been approved and is ready for your processing",
                        'link' => route('request-to-order.show', $order->id),
                        'approved_by' => $user->name,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            ActivityLogger::make($request)
                ->on($order)
                ->by($user)
                ->with(['order_no' => $order->order_no])
                ->logName('RequestToOrder')
                ->log("Order #{$order->order_no} approved by {$user->username}");
        });

        return response()->json([
            'success' => true,
            'message' => 'Request approved',
        ]);
    }

    public function forEod($id, HttpRequest $request): JsonResponse
    {
        $request->validate(['password' => 'required']);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password',
                'errors' => ['password' => 'Incorrect password'],
            ], 422);
        }

        $order = RequestToOrder::findOrFail($id);

        if ($order->status === 'forEOD') {
            return response()->json([
                'success' => false,
                'message' => 'Already sent for EOD approval',
                'errors' => ['status' => 'Already sent for EOD approval'],
            ], 422);
        }

        DB::transaction(function () use ($order, $user, $request) {
            $order->update(['status' => 'forEOD']);

            RequestToOrderApproval::create([
                'request_to_order_id' => $order->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'remarks' => 'Submitted for EOD approval',
            ]);

            $executiveDirectors = User::where('role', 'executive_director')->get();
            $creator = User::find($order->user_id);

            foreach ($executiveDirectors as $director) {
                DB::table('notifications')->insert([
                    'id' => (string) \Illuminate\Support\Str::uuid(),
                    'type' => 'OrderForEOD',
                    'notifiable_type' => 'App\Models\User',
                    'notifiable_id' => $director->id,
                    'data' => json_encode([
                        'order_id' => $order->id,
                        'title' => 'Order Ready for EOD Approval',
                        'order_no' => $order->order_no,
                        'amount' => $order->total_amount ?? 0,
                        'created_by' => $creator?->name ?? 'Unknown',
                        'department' => $order->department->name ?? 'N/A',
                        'purpose' => $order->purpose,
                        'previous_status' => $order->getOriginal('status'),
                        'status' => 'forEOD',
                        'message' => "Order #{$order->order_no} needs your review.",
                        'link' => route('request-to-order.show', $order->id),
                        'submitted_by' => $user->name,
                    ]),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            ActivityLogger::make($request)
                ->on($order)
                ->by($user)
                ->with(['order_no' => $order->order_no])
                ->logName('RequestToOrder')
                ->log("Order #{$order->order_no} submitted for EOD by {$user->username}");
        });

        return response()->json([
            'success' => true,
            'message' => 'Request sent for EOD approval',
        ]);
    }

    public function reject($id, HttpRequest $request): JsonResponse
    {
        $request->validate(['password' => 'required']);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Incorrect password',
                'errors' => ['password' => 'Incorrect password'],
            ], 422);
        }

        $order = RequestToOrder::findOrFail($id);

        if ($order->status === 'approved') {
            return response()->json([
                'success' => false,
                'message' => 'Already approved',
                'errors' => ['status' => 'Already approved'],
            ], 422);
        }

        $order->update(['status' => 'rejected']);

        ActivityLogger::make($request)
            ->on($order)
            ->log("Order #{$order->order_no} rejected by {$user->username}");

        return response()->json([
            'success' => true,
            'message' => 'Request rejected',
        ]);
    }

    public function releaseData($id): JsonResponse
    {
        $order = RequestToOrder::with(['details' => function ($query) {
            $query->whereRaw('quantity > COALESCE((SELECT SUM(quantity_released) FROM request_to_order_releases WHERE request_to_order_detail_id = request_to_order_details.id), 0)')
                ->withSum('releases', 'quantity_released');
        }, 'details.releases'])->findOrFail($id);

        $order->details->each(function ($detail) {
            $detail->remaining_quantity = $detail->quantity - ($detail->releases_sum_quantity_released ?? 0);
        });

        return response()->json([
            'success' => true,
            'data' => compact('order'),
        ]);
    }

    public function release(HttpRequest $request, $id): JsonResponse
    {
        $validated = $request->validate([
            'release_date' => 'required|date',
            'items' => 'required|array|min:1',
            'items.*.detail_id' => 'required|exists:request_to_order_details,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.notes' => 'nullable|string',
        ]);

        $order = RequestToOrder::findOrFail($id);

        try {
            DB::transaction(function () use ($validated, $order, $request) {
                foreach ($validated['items'] as $item) {
                    $detail = RequestToOrderDetail::withSum('releases', 'quantity_released')
                        ->findOrFail($item['detail_id']);

                    $alreadyReleased = $detail->releases_sum_quantity_released ?? 0;
                    $attemptingToRelease = $item['quantity'];
                    $totalAfterRelease = $alreadyReleased + $attemptingToRelease;

                    if ($totalAfterRelease > $detail->quantity) {
                        $remaining = $detail->quantity - $alreadyReleased;
                        throw new \Exception(
                            "Cannot release {$attemptingToRelease}. " .
                            "Already released {$alreadyReleased} of {$detail->quantity}. " .
                            "Only {$remaining} remaining for item: {$detail->item_description}"
                        );
                    }

                    $release = RequestToOrderRelease::create([
                        'request_to_order_id' => $order->id,
                        'request_to_order_detail_id' => $item['detail_id'],
                        'quantity_released' => $item['quantity'],
                        'release_date' => $validated['release_date'],
                        'notes' => $item['notes'] ?? null,
                        'released_by' => auth()->id(),
                    ]);

                    ActivityLogger::make($request)
                        ->on($release)
                        ->by(auth()->user())
                        ->with([
                            'order_no' => $order->order_no,
                            'detail_id' => $detail->id,
                            'released_quantity' => $item['quantity'],
                        ])
                        ->logName('RequestToOrderRelease')
                        ->log("Released {$item['quantity']} units for item: {$detail->item_description}");
                }

                RequestToOrderApproval::create([
                    'request_to_order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'status' => 'approved',
                    'remarks' => 'Released items',
                    'approved_at' => now(),
                ]);

                $order->load('details.releases');
                $allReleased = $order->details->every(fn($d) => $d->releases->sum('quantity_released') >= $d->quantity);

                if ($allReleased) {
                    $order->update(['status' => 'completed']);

                    ActivityLogger::make($request)
                        ->on($order)
                        ->by(auth()->user())
                        ->with(['order_no' => $order->order_no])
                        ->logName('RequestToOrder')
                        ->log('RequestToOrder marked as completed after full release');
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Items released successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('RequestToOrder release failed: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to release items',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    private function generateOrderNumber(): string
    {
        $prefix = 'ORD-';
        $datePrefix = now()->format('Ym');

        $latest = RequestToOrder::where('order_no', 'like', $prefix . $datePrefix . '%')
            ->whereRaw("LENGTH(order_no) = ?", [strlen($prefix . $datePrefix) + 4])
            ->orderBy('order_no', 'desc')
            ->first();

        $number = $latest
            ? (int) substr($latest->order_no, -4) + 1
            : 1;

        return $prefix . $datePrefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
