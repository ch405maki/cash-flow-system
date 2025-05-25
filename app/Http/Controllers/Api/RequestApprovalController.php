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

use App\Models\Department;
use App\Models\PurchaseOrderDetail;

class RequestApprovalController extends Controller
{
    public function index() 
    {
        $user = Auth::user();

        $requests = RequestToOrder::with('details')
            ->whereIn('status', ['for_eod'])
            ->get();

        return Inertia::render('Request/ForApproval/Index', [
            'requests' => $requests,
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
            ],
        ]);
    }
}
