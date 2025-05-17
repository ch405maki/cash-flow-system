<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

use App\Models\Department;
use App\Models\PurchaseOrderDetail;

class RequestToOrderController extends Controller
{
    public function index()
    {
        $requests = Request::with(['department', 'user', 'details'])
            ->where('status', 'request to order')
            ->get();    

        return Inertia::render('Request/Order/Index', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => Auth::id(),
                'department_id' => Auth::user()->department_id,
            ],
        ]);
    }

}
