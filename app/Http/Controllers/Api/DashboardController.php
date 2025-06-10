<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Request;
use App\Models\RequestToOrder;
use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Http\Request as HttpRequest;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
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
        
        // Default dashboard for other roles
        return Inertia::render('Dashboard/Index', [
            'userRole' => $user->role,
        ]);
    }
    
    protected function departmentDashboard()
    {
        $user = auth()->user();
        
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
        $user = auth()->user();
        
        $requests = Request::with(['user', 'details'])
            ->where('status', 'to_property')
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
            'pending' => Request::where('status', 'to_property')->count(),
            'to_order' => Request::where('status', 'to_order')->count(),
            'approval' => RequestToOrder::where('status', 'pending')->count(),
            'rejected' => RequestToOrder::where('status', 'rejected')->count(),
        ];
        
        return Inertia::render('Dashboard/Custodian/Index', [
            'isDepartmentUser' => true,
            'recentRequests' => $requests,
            'statusCounts' => $statusCounts,
            'userRole' => $user->role,
            'username' => $user->username,
        ]);
    }

    protected function executiveDashboard()
    {
        $user = auth()->user();
        
        $requests = Request::with(['user', 'details'])
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
                'totalRequest' => Request::whereMonth('created_at', Carbon::now()->month)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->count(),
                'totalPO' => PurchaseOrder::whereMonth('created_at', Carbon::now()->month)
                            ->where('status', 'approved')
                            ->whereYear('created_at', Carbon::now()->year)
                            ->count(),
                'toOrderApproval' => RequestToOrder::where('status', 'for_eod')->count(),
                'poApproval' => PurchaseOrder::where('status', 'for_approval')->count(),
                'rejected' => RequestToOrder::where('status', 'rejected')->count(),
            ];
        
        return Inertia::render('Dashboard/Executive/Index', [
            'isDepartmentUser' => true,
            'recentRequests' => $requests,
            'statusCounts' => $statusCounts,
            'userRole' => $user->role,
            'username' => $user->username,
        ]);
    }
}