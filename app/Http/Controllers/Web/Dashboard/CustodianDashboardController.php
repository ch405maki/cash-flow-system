<?php

namespace App\Http\Controllers\Web\Dashboard;

use Inertia\Inertia;
use App\Models\Request;
use App\Models\RequestToOrder;
use App\Models\RequestToOrderDetail;
use Illuminate\Support\Facades\DB;

class CustodianDashboardController extends BaseDashboardController
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function index()
    {
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
            'userRole' => $this->user->role,
            'username' => $this->user->username,
        ]);
    }
}