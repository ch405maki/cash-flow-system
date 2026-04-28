<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;

class ReceivingController extends Controller
{

public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status');

        $query = PurchaseOrder::with(['user', 'department', 'account', 'details'])
                ->whereIn('status', ['approved', 'voucherCreated'])
                ->latest();

        $purchaseOrders = $query->paginate(10);

        return Inertia::render('PurchaseOrders/Receiving/Index', [
            'purchaseOrders' => $purchaseOrders,
        ]);
    }
}
