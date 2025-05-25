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

class RequestToOrderController extends Controller
{
    public function index() 
    {
        $user = Auth::user();

        $requests = RequestToOrder::with('details')
            ->whereIn('status', ['pending', 'for_eod'])
            ->get();

        $forOrders = Request::with(['department', 'user', 'details'])
            ->whereIn('status', [ 'to_order'])
            ->get();

        return Inertia::render('Request/Order/Index', [
            'requests' => $requests,
            'forOrders' => $forOrders,
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }


    public function create()
    {
        $requests = Request::with(['details', 'department', 'user'])
            ->where('status', 'to_order')
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

    public function show($id)
    {
        $user = Auth::user();

        $requestOrder = RequestToOrder::with([
            'details.request.department'
        ])->findOrFail($id);

        return Inertia::render('Request/Order/Show', [
            'requestOrder' => $requestOrder,
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'access' => $user->access_id,
            ],
        ]);
    }

    public function approve($id, HttpRequest $request)
    {
        $request->validate(['password' => 'required']);
        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $order = RequestToOrder::findOrFail($id);
        if ($order->status === 'for_po') {
            return back()->withErrors(['status' => 'Already approved']);
        }

        $order->update(['status' => 'for_po']);
        return back()->with('success', 'Request approved');
    }

    public function forEod($id, HttpRequest $request)
    {
        $request->validate(['password' => 'required']);
        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $order = RequestToOrder::findOrFail($id);
        if ($order->status === 'for_eod') {
            return back()->withErrors(['status' => 'Already sent for EOD approval']);
        }

        $order->update(['status' => 'for_eod']);
        return back()->with('success', 'Request sent for EOD approval');
    }


    public function reject($id, HttpRequest $request)
    {
        $request->validate(['password' => 'required']);
        $user = auth()->user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $order = RequestToOrder::findOrFail($id);
        if ($order->status === 'approved') {
            return back()->withErrors(['status' => 'Already approved']);
        }

        $order->update(['status' => 'rejected']);
        return back()->with('success', 'Request approved');
    }

}

