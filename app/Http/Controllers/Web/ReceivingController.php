<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\PurchaseOrderReceiving;

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

    // Additional methods for receiving process can be added here, marking items as received, etc.
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load([
            'details.receivings.receiver',
            'user',
            'department',
        ]);

        return Inertia::render('PurchaseOrders/Receiving/Show', [
            'purchaseOrder' => $purchaseOrder,
            'authUser'      => Auth::user(),
        ]);
    }

    public function store(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'items'                        => 'required|array|min:1',
            'items.*.po_detail_id'         => 'required|exists:purchase_order_details,id',
            'items.*.quantity_ordered'     => 'required|integer|min:1',
            'items.*.quantity_received'    => 'required|integer|min:0',
            'items.*.condition'            => 'nullable|in:good,damaged,incomplete',
            'items.*.received_date'        => 'required|date',
            'items.*.remarks'              => 'nullable|string|max:500',
        ]);

        foreach ($validated['items'] as $item) {
            PurchaseOrderReceiving::create([
                'po_id'             => $purchaseOrder->id,
                'po_detail_id'      => $item['po_detail_id'],
                'received_by'       => Auth::id(),
                'quantity_ordered'  => $item['quantity_ordered'],
                'quantity_received' => $item['quantity_received'],
                'condition'         => $item['condition'],
                'received_date'     => $item['received_date'],
                'remarks'           => $item['remarks'] ?? null,
            ]);
        }

        return back()->with('success', 'Items received successfully.');
    }
}
