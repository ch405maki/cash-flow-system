<?php

namespace App\Http\Controllers\Web\Dashboard;

use Inertia\Inertia;
use App\Models\Request;

class DepartmentDashboardController extends BaseDashboardController
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

        $statusCounts = [
            'pending' => Request::where('department_id', $this->user->department_id)
                ->where('status', 'pending')->count(),
            'approved' => Request::where('department_id', $this->user->department_id)
                ->where('status', 'approved')->count(),
            'to_order' => Request::where('department_id', $this->user->department_id)
                ->where('status', 'to_order')->count(),
            'rejected' => Request::where('department_id', $this->user->department_id)
                ->where('status', 'rejected')->count(),
            'total' => Request::where('department_id', $this->user->department_id)->count(),
        ];

        return Inertia::render('Dashboard/Department/Index', [
            'isDepartmentUser' => true,
            'recentRequests' => $requests,
            'statusCounts' => $statusCounts,
            'userRole' => $this->user->role,
            'username' => $this->user->username,
        ]);
    }
}