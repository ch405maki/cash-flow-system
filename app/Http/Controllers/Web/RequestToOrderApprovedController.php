<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use App\Models\RequestToOrder;

class RequestToOrderApprovedController extends Controller
{

    public function index()
    {
        $requests = RequestToOrder::with('details')
            ->where('status', 'forPO')
            ->get();

        return Inertia::render('RequestToOrder/Index', [
            'requests' => $requests,
        ]);
    }


}
