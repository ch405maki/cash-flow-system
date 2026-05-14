<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\RequestDetail;
use App\Models\RequestToOrder;
use App\Http\Requests\StoreRequestToOrderRequest;
use Illuminate\Support\Facades\DB;
use App\Models\RequestToOrderApproval;
use Illuminate\Support\Facades\Log;
use App\Models\User;

use App\Models\Department;
use App\Models\PurchaseOrderDetail;
use App\Services\ActivityLogger;

class RequestToOrderController extends Controller
{
    public function index() 
    {
        $user = Auth::user();

        $requests = RequestToOrder::with('details')
            ->whereIn('status', ['pending'])
            ->get();

        $forOrders = Request::with(['department', 'user', 'details'])
            ->whereIn('status', [ 'to_order'])
            ->get();

        return Inertia::render('Request/Order/Index', [
            'requests' => $requests,
            'forOrders' => $forOrders,
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }

    public function create()
    {
        $requests = Request::with(['details', 'department', 'user'])
            ->where('status', 'to_order')
            ->get();

        return Inertia::render('Request/Order/Create', [
            'requests' => $requests
        ]);
    }

    public function list()
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

        return Inertia::render('Request/Order/ListToOrder', [
            'requests' => $requests
        ]);
    }

    public function store(HttpRequest $request)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.item_description' => 'nullable|string|max:255',
            'items.*.detail_id' => 'required|exists:request_details,id',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $authUser = auth()->user();

            // 1. Create RequestToOrder
            $order = RequestToOrder::create([
                'user_id' => $authUser->id,
                'order_no' => $this->generateOrderNumber(),
                'order_date' => now(),
                'notes' => $validated['notes'],
                'status' => 'pending',
            ]);

            // 2. Create each order detail WITH request_detail_id
            foreach ($validated['items'] as $item) {
                $order->details()->create([
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'item_description' => $item['item_description'] ?? null,
                    'request_detail_id' => $item['detail_id'], // ← ADD THIS LINE
                ]);

                // Update both tagging AND tracking_status
                RequestDetail::where('id', $item['detail_id'])
                    ->update([
                        'tagging' => 'forPurchase',
                        'tracking_status' => 'ordered' // ← UPDATE STATUS TO 'ordered'
                    ]);
            }

            // 3. Automatically create approval record by creator
            RequestToOrderApproval::create([
                'request_to_order_id' => $order->id,
                'user_id' => $authUser->id,
                'status' => 'pending',
                'approved_at' => now(),
                'remarks' => $validated['notes'],
            ]);

            // 4. Log the creation action
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

        return redirect()->route('request-to-order.index')
            ->with('success', 'Order created successfully');
    }

    public function storeManual(HttpRequest $request)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.item_description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $authUser = auth()->user();

            // 1. Create the RequestToOrder
            $order = RequestToOrder::create([
                'user_id' => $authUser->id,
                'order_no' => $this->generateOrderNumber(),
                'order_date' => now(),
                'notes' => $validated['notes'],
                'status' => 'pending',
            ]);

            // 2. Add each detail manually
            foreach ($validated['items'] as $item) {
                $order->details()->create([
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'item_description' => $item['item_description'] ?? null,
                ]);
            }

            // 3. Add approval record (auto-approved by creator)
            RequestToOrderApproval::create([
                'request_to_order_id' => $order->id,
                'user_id' => $authUser->id,
                'status' => 'pending',
                'approved_at' => now(),
                'remarks' => $validated['notes'],
            ]);

            // 4. Log activity
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

        return redirect()->route('request-to-order.index')
            ->with('success', 'Order created successfully');
    }

    private function generateOrderNumber()
    {
        $prefix = 'ORD-';
        $datePrefix = now()->format('Ym'); // YYYYMM

        // Get latest strictly matching ORD-YYYYMM##### format
        $latest = RequestToOrder::where('order_no', 'like', $prefix . $datePrefix . '%')
            ->whereRaw("LENGTH(order_no) = ?", [strlen($prefix . $datePrefix) + 4])
            ->orderBy('order_no', 'desc')
            ->first();

        $number = $latest
            ? (int) substr($latest->order_no, -4) + 1 
            : 1;

        return $prefix . $datePrefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    public function show($id)
    {
        $user = Auth::user();

        $requestOrder = RequestToOrder::with([
            'details.requestDetail.request.department',
            'details.releases.releasedBy',
            'details.request.department',
            'approvals.user',
        ])->findOrFail($id);

        return Inertia::render('Request/Order/Show', [
            'requestOrder' => $requestOrder,
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'access' => $user->access_id,
            ],
        ]);
    }

    public function approve($id, HttpRequest $request)
    {
        $request->validate(['password' => 'required']);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $order = RequestToOrder::findOrFail($id);

        if ($order->status === 'forPO') {
            return back()->withErrors(['status' => 'Already approved']);
        }

        DB::transaction(function () use ($order, $user, $request) {
            $order->update(['status' => 'forPO']);

            // 2. Record the approval
            RequestToOrderApproval::create([
                'request_to_order_id' => $order->id,
                'user_id' => $user->id,
                'status' => 'approved',
                'remarks' => 'Approved by Executive Director',
                'approved_at' => now(),
            ]);

            // ===== NOTIFICATION FOR CREATOR (PROPERTY CUSTODIAN) =====
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

            // ===== NOTIFICATION FOR PURCHASING ROLE =====
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
            // ===== END NOTIFICATIONS =====

            // 3. Log activity
            ActivityLogger::make($request)
                ->on($order)
                ->by($user)
                ->with([
                    'order_no' => $order->order_no,
                ])
                ->logName('RequestToOrder')
                ->log("Order #{$order->order_no} approved by {$user->username}");
        });

        return back()->with('success', 'Request approved');
    }

    public function forEod($id, HttpRequest $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $order = RequestToOrder::findOrFail($id);

        if ($order->status === 'forEOD') {
            return back()->withErrors(['status' => 'Already sent for EOD approval']);
        }

        DB::transaction(function () use ($order, $user, $request) {
            $order->update(['status' => 'forEOD']);

            // 2. Create approval record (pending)
            RequestToOrderApproval::create([
                'request_to_order_id' => $order->id,
                'user_id' => $user->id,
                'status' => 'pending',
                'remarks' => 'Submitted for EOD approval',
            ]);

            // ===== NOTIFICATION FOR EXECUTIVE DIRECTOR ROLE =====
            // Get all users with role 'executive_director'
            $executiveDirectors = User::where('role', 'executive_director')->get();
            
            // Get the creator of the order
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
            // ===== END NOTIFICATIONS =====

            // 3. Log the activity
            ActivityLogger::make($request)
                ->on($order)
                ->by($user)
                ->with([
                    'order_no' => $order->order_no,
                ])
                ->logName('RequestToOrder')
                ->log("Order #{$order->order_no} submitted for EOD by {$user->username}");
        });

        return back()->with('success', 'Request sent for EOD approval');
    }

    public function reject($id, HttpRequest $request)
    {
        $request->validate(['password' => 'required']);
        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $order = RequestToOrder::findOrFail($id);
        if ($order->status === 'approved') {
            return back()->withErrors(['status' => 'Already approved']);
        }

        $order->update(['status' => 'rejected']);

        ActivityLogger::make($request)
            ->on($order)
            ->log("Order #{$order->order_no} rejected by {$user->username}");

        return back()->with('success', 'Request approved');
    }
}

