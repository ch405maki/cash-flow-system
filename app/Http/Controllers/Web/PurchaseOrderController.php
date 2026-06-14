<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request as HttpRequest;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    public function index(HttpRequest $request)
    {
        return Inertia::render('PurchaseOrders/Index', [
            'filters' => $request->only(['status']),
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        return Inertia::render('PurchaseOrders/Show', [
            'purchaseOrderId' => $purchaseOrder->id,
        ]);
    }

    public function create()
    {
        return Inertia::render('PurchaseOrders/Create', [
            'canvas_id' => request()->query('canvas_id'),
        ]);
    }
}
