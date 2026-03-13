<?php

namespace App\Http\Controllers\Web\Dashboard;

use Inertia\Inertia;
use App\Models\Request;
use App\Models\RequestToOrder;
use App\Models\PurchaseOrder;
use App\Models\Voucher;

class ExecutiveDashboardController extends BaseDashboardController
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function index()
    {
        $approvalTimeTrends = $this->getApprovalTimeTrends();

        $requests = Request::with(['user', 'details'])
            ->where('status', 'propertyCustodian')
            ->orderBy('request_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'request_no' => $request->request_no,
                    'request_date' => $request->request_date,
                    'purpose' => $request->purpose,
                    'status' => $request->status,
                    'user' => $request->user ? $request->user->only(['first_name', 'last_name']) : null,
                    'details' => $request->details->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'quantity' => $detail->quantity,
                            'unit' => $detail->unit,
                            'item_description' => $detail->item_description,
                        ];
                    }),
                ];
            });

        $requestToOrders = RequestToOrder::with(['user', 'details'])
            ->where('status', 'forEOD')
            ->orderBy('order_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'order_no' => $order->order_no,
                    'order_date' => $order->order_date,
                    'notes' => $order->notes,
                    'status' => $order->status,
                    'user' => $order->user ? $order->user->only(['first_name', 'last_name']) : null,
                    'details' => $order->details->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'quantity' => $detail->quantity,
                            'unit' => $detail->unit,
                            'item_description' => $detail->item_description,
                        ];
                    }),
                ];
            });

        $purchaseOrders = PurchaseOrder::with(['user', 'department', 'account', 'details'])
            ->orderBy('date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($po) {
                return [
                    'id' => $po->id,
                    'po_no' => $po->po_no,
                    'date' => $po->date,
                    'payee' => $po->payee,
                    'amount' => $po->amount,
                    'purpose' => $po->purpose,
                    'status' => $po->status,
                    'user' => $po->user ? $po->user->only(['first_name', 'last_name']) : null,
                    'department' => $po->department ? $po->department->only(['name']) : null,
                    'account' => $po->account ? $po->account->only(['name']) : null,
                    'details' => $po->details->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'quantity' => $detail->quantity,
                            'unit' => $detail->unit,
                            'item_description' => $detail->item_description,
                            'unit_price' => $detail->unit_price,
                            'amount' => $detail->amount,
                        ];
                    }),
                ];
            });

        $vouchers = Voucher::with(['user', 'details'])
            ->whereIn('status', ['forEOD'])
            ->get();

        $statusCounts = [
            'totalRequest' => Request::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
            'totalPO' => PurchaseOrder::whereMonth('created_at', now()->month)
                ->where('status', 'approved')
                ->whereYear('created_at', now()->year)->count(),
            'toOrderApproval' => RequestToOrder::where('status', 'forEOD')->count(),
            'poApproval' => PurchaseOrder::where('status', 'forEOD')->count(),
            'rejected' => RequestToOrder::where('status', 'rejected')->count(),
            'totalRequestToOrder' => RequestToOrder::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
            'totalPurchaseOrderAmount' => PurchaseOrder::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->sum('amount'),
            'pendingRequestToOrder' => RequestToOrder::where('status', 'pending')->count(),
        ];

        $monthlyMetrics = [
            'requests' => $this->getMonthlyCounts(Request::class),
            'requestToOrders' => $this->getMonthlyCounts(RequestToOrder::class),
            'purchaseOrders' => $this->getMonthlyCounts(PurchaseOrder::class),
            'purchaseOrderAmounts' => $this->getMonthlyAmounts(),
        ];

        return Inertia::render('Dashboard/Executive/Index', [
            'isDepartmentUser' => true,
            'recentRequests' => $requests,
            'recentRequestToOrders' => $requestToOrders,
            'recentPurchaseOrders' => $purchaseOrders,
            'vouchers' => $vouchers,
            'statusCounts' => $statusCounts,
            'approvalTimeTrends' => $approvalTimeTrends,
            'monthlyMetrics' => $monthlyMetrics,
            'userRole' => $this->user->role,
            'username' => $this->user->username,
        ]);
    }
}