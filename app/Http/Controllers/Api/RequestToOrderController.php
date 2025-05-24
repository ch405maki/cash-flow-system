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
            'request_id' => 'required|exists:requests,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:request_details,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.supplier' => 'nullable|string|max:255',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        // Create the order
        $order = RequestToOrder::create([
            'user_id' => auth()->id(),
            'order_no' => $this->generateOrderNumber(),
            'order_date' => now(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending'
        ]);

        // Get the original request for department info
        $originalRequest = Request::findOrFail($validated['request_id']);

        // Add the selected items
        foreach ($validated['items'] as $item) {
            $requestDetail = RequestDetail::findOrFail($item['id']);

            $order->details()->create([
                'request_id' => $validated['request_id'],
                'department_id' => $originalRequest->department_id,
                'quantity' => $item['quantity'],
                'unit' => $requestDetail->unit,
                'item_description' => $requestDetail->item_description,
                'supplier' => $item['supplier'] ?? null,
                'unit_price' => $item['unit_price'] ?? null,
                // total_price will be auto-calculated by the model
            ]);

            // Update released quantity in the original request
            $requestDetail->increment('released_quantity', $item['quantity']);
        }

        return redirect()->route('request-to-orders.show', $order->id)
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

