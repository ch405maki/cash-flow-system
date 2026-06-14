<?php

namespace App\Http\Controllers\Web;

use Inertia\Inertia;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;

use App\Models\RequestToOrder;

class RequestToOrderController extends Controller
{
    public function index()
    {
        $pageType = request('pageType', 'index');
        $page = $pageType === 'on-process-orders' ? 'Request/Index' : 'RequestToOrder/Index';

        return Inertia::render($page, compact('pageType'));
    }

    public function create()
    {
        return Inertia::render('RequestToOrder/Create');
    }

    public function list()
    {
        return Inertia::render('RequestToOrder/ListToOrder');
    }

    public function show(RequestToOrder $order)
    {
        return Inertia::render('RequestToOrder/Show', [
            'orderId' => $order->id,
        ]);
    }

    public function releaseCreate(RequestToOrder $order)
    {
        return Inertia::render('RequestToOrder/Release/Create', [
            'orderId' => $order->id,
        ]);
    }
}
