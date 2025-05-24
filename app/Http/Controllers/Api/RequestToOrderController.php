<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request as HttpRequest;
use App\Models\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use App\Models\RequestDetail;
use App\Models\RequestToOrder;
use App\Http\Requests\StoreRequestToOrderRequest;

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

    public function create()
    {
        $requests = Request::with(['details', 'department', 'user'])
            ->where('status', 'approved')
            ->get();

        return Inertia::render('Request/Order/Create', [
            'requests' => $requests
        ]);
    }

    public function store(HttpRequest $request)
{
    $validated = $request->validate([
        'notes' => 'nullable|string',
        'items' => 'required|array|min:1',
        'items.*.request_id' => 'required|exists:requests,id',
        'items.*.department_id' => 'required|exists:departments,id', 
        'items.*.detail_id' => 'required|exists:request_details,id',
        'items.*.quantity' => 'required|numeric|min:0.01',
        'items.*.unit' => 'required|string',
        'items.*.item_description' => 'required|string',
    ]);

    // Create the order
    $order = RequestToOrder::create([
        'user_id' => auth()->id(),
        'order_no' => 'ORD-' . now()->format('YmdHis'),
        'order_date' => now(),
        'notes' => $validated['notes'],
        'status' => 'pending'
    ]);

    // Add order items
    foreach ($validated['items'] as $item) {
        $order->details()->create([
            'request_id' => $item['request_id'],
            'department_id' => $item['department_id'],
            'request_detail_id' => $item['detail_id'],
            'quantity' => $item['quantity'],
            'unit' => $item['unit'],
            'item_description' => $item['item_description']
        ]);

        // Update released quantity in original request
        // RequestDetail::where('id', $item['detail_id'])
        //     ->increment('released_quantity', $item['quantity']);
    }

    return redirect()->route('request-to-order.index')
        ->with('success', 'Order created successfully');
}

    private function generateOrderNumber()
    {
        $prefix = 'ORD-';
        $date = now()->format('Ymd');
        $latest = RequestToOrder::where('order_no', 'like', $prefix.$date.'%')
            ->orderBy('order_no', 'desc')
            ->first();

        $number = $latest ? (int)substr($latest->order_no, -4) + 1 : 1;
        
        return $prefix.$date.str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}

