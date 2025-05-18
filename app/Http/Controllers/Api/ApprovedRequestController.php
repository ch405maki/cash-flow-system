<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\Department;
use App\Models\PurchaseOrderDetail;
use App\Models\Account;

class ApprovedRequestController extends Controller
{
    public function index()
    {
        $requests = Request::with(['department', 'user', 'details'])
            ->where('status', 'approved')
            ->get();

        return Inertia::render('Request/Approved/Index', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => Auth::id(),
                'department_id' => Auth::user()->department_id,
            ],
        ]);
    }

    public function show(request $request)
    {
        return Inertia::render('Request/Approved/Show', [
            'request' => $request->load(['user', 'department', 'details']),
            'accounts' => Account::all(['id', 'account_title']),
        ]);
    }
}
