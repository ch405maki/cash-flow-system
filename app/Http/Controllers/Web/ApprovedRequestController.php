<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\Request;
use App\Models\Account;
use App\Models\RequestToOrder;

class ApprovedRequestController extends Controller
{

    public function index()
    {
        $requests = RequestToOrder::with('details')
            ->where('status', 'forPO')
            ->get();

        return Inertia::render('Request/Approved/Index', [
            'requests' => $requests,
        ]);
    }


    public function show(Request $request)
    {
        return Inertia::render('Request/Approved/Show', [
            'request' => $request->load(['user', 'department', 'details']),
            'accounts' => Account::all(['id', 'account_title']),
        ]);
    }


}
