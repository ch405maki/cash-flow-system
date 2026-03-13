<?php

namespace App\Http\Controllers\Web\Dashboard;

use Inertia\Inertia;
use App\Models\Request;
use App\Models\PettyCash;
use App\Models\PettyCashFund;
use Illuminate\Support\Facades\Auth;

class BursarDashboardController extends BaseDashboardController
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function index()
    {
        $requests = Request::with(['user', 'details'])
            ->where('department_id', $this->user->department_id)
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

        $pettyCashFund = PettyCashFund::where('user_id', Auth::id())->first();

        $pettyCash = PettyCash::with('items', 'user')
            ->where('status', 'audited')
            ->where('pcv_no', 'LIKE', 'PCV-%')
            ->orderBy('created_at', 'desc')
            ->get();

        $pettyCashRequest = PettyCash::with('items', 'user')
            ->where('status', 'audited')
            ->where('pcv_no', 'LIKE', 'PCV-%')
            ->count();

        $released = PettyCash::with('items', 'user')
            ->where('status', 'released')
            ->count();

        return Inertia::render('Dashboard/Bursar/Index', [
            'isDepartmentUser' => true,
            'userRole' => $this->user->role,
            'username' => $this->user->username,
            'pettyCashRequest' => $pettyCashRequest,
            'pettyCash' => $pettyCash,
            'pettyCashFund' => $pettyCashFund,
            'totalreleased' => $released,
            'totalRequests' => Request::where('department_id', $this->user->department_id)->count(),
        ]);
    }
}