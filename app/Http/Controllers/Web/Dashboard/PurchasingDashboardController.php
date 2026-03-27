<?php

namespace App\Http\Controllers\Web\Dashboard;

use Inertia\Inertia;
use App\Models\RequestToOrder;
use App\Models\RequestToOrderDetail;
use App\Models\Canvas;
use App\Models\PurchaseOrder;
use App\Models\PettyCash;
use Illuminate\Support\Facades\DB;

class PurchasingDashboardController extends BaseDashboardController
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function index()
    {
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

        $pettyCash = PettyCash::with(['items', 'user.department'])
            ->where('status', 'for liquidation')
            ->whereHas('user', function ($q) {
                $q->where('department_id', $this->user->department_id);
            })
            ->orderBy('date', 'desc')
            ->get();

        return Inertia::render('Dashboard/Purchasing/Index', [
            'isDepartmentUser' => true,
            'recentRequests' => $requests,
            'statusCounts' => $statusCounts,
            'frequentItems' => $frequentItems,
            'userRole' => $this->user->role,
            'username' => $this->user->username,
            'pettyCash' => $pettyCash,
        ]);
    }
}