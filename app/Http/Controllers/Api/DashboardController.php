<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Request;
use App\Models\RequestToOrder;
use App\Models\RequestToOrderDetail;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\Canvas;
use Illuminate\Http\Request as HttpRequest;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Redirect to appropriate dashboard based on user role
        if (in_array($user->role, ['staff', 'department_head'])) {
            return $this->departmentDashboard();
        }
        elseif ($user->role === 'property_custodian') {
            return $this->custodianDashboard();
        }
        elseif ($user->role === 'executive_director') {
            return $this->executiveDashboard();
        }
        elseif ($user->role === 'purchasing') {
            return $this->purchasingDashboard();
        }
        elseif ($user->role === 'accounting') {
            return $this->accountingDashboard();
        }
        
        // Default dashboard for other roles
        return Inertia::render('Dashboard/Index', [
            'userRole' => $user->role,
        ]);
    }
    
    protected function accountingDashboard()
    {
        $user = Auth::user();
        
        $purchaseOrders = PurchaseOrder::with(['user', 'department', 'details'])
        ->where('department_id', $user->department_id)
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
                'department' => $po->department ? $po->department->name : null,
                'user' => $po->user ? $po->user->only(['first_name', 'last_name']) : null,
                'details' => $po->details->map(function ($detail) {
                    return [
                        'id' => $detail->id,
                        'quantity' => $detail->quantity,
                        'unit' => $detail->unit,
                        'item_description' => $detail->item_description,
                        'unit_price' => $detail->unit_price,
                        'amount' => $detail->amount
                    ];
                }),
            ];
        });
            
        $statusCounts = [
            'pending' => Request::where('department_id', $user->department_id)
                ->where('status', 'pending')->count(),
            'approved' => Request::where('department_id', $user->department_id)
                ->where('status', 'approved')->count(),
            'to_order' => Request::where('department_id', $user->department_id)
                ->where('status', 'to_order')->count(),
            'rejected' => Request::where('department_id', $user->department_id)
                ->where('status', 'rejected')->count(),
            'total' => Request::where('department_id', $user->department_id)->count(),
        ];
        
        return Inertia::render('Dashboard/Accounting/Index', [
            'isDepartmentUser' => true,
            'purchaseOrders' => $purchaseOrders,
            'statusCounts' => $statusCounts,
            'userRole' => $user->role,
            'username' => $user->username,
        ]);
    }

    protected function departmentDashboard()
    {
        $user = Auth::user();
        
        $requests = Request::with(['user', 'details'])
            ->where('department_id', $user->department_id)
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
            
        $statusCounts = [
            'pending' => Request::where('department_id', $user->department_id)
                ->where('status', 'pending')->count(),
            'approved' => Request::where('department_id', $user->department_id)
                ->where('status', 'approved')->count(),
            'to_order' => Request::where('department_id', $user->department_id)
                ->where('status', 'to_order')->count(),
            'rejected' => Request::where('department_id', $user->department_id)
                ->where('status', 'rejected')->count(),
            'total' => Request::where('department_id', $user->department_id)->count(),
        ];
        
        return Inertia::render('Dashboard/Department/Index', [
            'isDepartmentUser' => true,
            'recentRequests' => $requests,
            'statusCounts' => $statusCounts,
            'userRole' => $user->role,
            'username' => $user->username,
        ]);
    }

    protected function custodianDashboard()
    {
        $user = Auth::user();
        
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
            
        $statusCounts = [
            'pending' => Request::where('status', 'propertyCustodian')->count(),
            'to_order' => Request::where('status', 'to_order')->count(),
            'approval' => RequestToOrder::where('status', 'pending')->count(),
            'rejected' => RequestToOrder::where('status', 'rejected')->count(),
        ];

        $frequentItems = RequestToOrderDetail::select(
            'item_description',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('COUNT(*) as request_count')
        )
        ->groupBy('item_description')
        ->orderByDesc('request_count')
        ->limit(10)
        ->get();
        
        return Inertia::render('Dashboard/Custodian/Index', [
            'isDepartmentUser' => true,
            'recentRequests' => $requests,
            'statusCounts' => $statusCounts,
            'frequentItems' => $frequentItems,
            'userRole' => $user->role,
            'username' => $user->username,
        ]);
    }

    protected function purchasingDashboard()
    {
        $user = Auth::user();
        
        $requests = RequestToOrder::with(['user', 'details'])
            ->where('status', 'forPO')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'order_no' => $request->order_no,
                    'request_date' => $request->order_date,
                    'note' => $request->note,
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
            
        $statusCounts = [
            'approved_request' => RequestToOrder::where('status', 'forPO')->count(),
            'canvas_approval' => Canvas::where('status', 'forEOD')->count(),
            'po_approval' => PurchaseOrder::where('status', 'forEOD')->count(),
            'approved_po' => PurchaseOrder::where('status', 'approved')->count(),
        ];
        
        $frequentItems = RequestToOrderDetail::select(
            'item_description',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('COUNT(*) as request_count')
        )
        ->groupBy('item_description')
        ->orderByDesc('request_count')
        ->limit(10)
        ->get();
        
        return Inertia::render('Dashboard/Purchasing/Index', [
            'isDepartmentUser' => true,
            'recentRequests' => $requests,
            'statusCounts' => $statusCounts,
            'frequentItems' => $frequentItems,
            'userRole' => $user->role,
            'username' => $user->username,
        ]);
    }

    protected function executiveDashboard()
    {
        $user = Auth::user();
        
        // Recent Requests
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
        
        // Recent RequestToOrders
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
        
        // Recent PurchaseOrders
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
        
        // Status Counts
        $statusCounts = [
            'totalRequest' => Request::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->count(),
            'totalPO' => PurchaseOrder::whereMonth('created_at', now()->month)
                        ->where('status', 'approved')
                        ->whereYear('created_at', now()->year)
                        ->count(),
            'toOrderApproval' => RequestToOrder::where('status', 'forEOD')->count(),
            'poApproval' => PurchaseOrder::where('status', 'forEOD')->count(),
            'rejected' => RequestToOrder::where('status', 'rejected')->count(),
            
            // Additional metrics
            'totalRequestToOrder' => RequestToOrder::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->count(),
            'totalPurchaseOrderAmount' => PurchaseOrder::whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year)
                        ->sum('amount'),
            'pendingRequestToOrder' => RequestToOrder::where('status', 'pending')->count(),
        ];
        
        // Monthly metrics
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
            'statusCounts' => $statusCounts,
            'monthlyMetrics' => $monthlyMetrics,
            'userRole' => $user->role,
            'username' => $user->username,
        ]);
    }

    // Helper method to get monthly counts
    protected function getMonthlyCounts($model)
    {
        return $model::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
    }

    // Helper method to get monthly purchase order amounts
    protected function getMonthlyAmounts()
    {
        return PurchaseOrder::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }
}