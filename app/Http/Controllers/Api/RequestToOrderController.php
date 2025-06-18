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
use Illuminate\Support\Facades\DB;


use App\Models\Department;
use App\Models\PurchaseOrderDetail;

class RequestToOrderController extends Controller
{
    public function index() 
    {
        $user = Auth::user();

        $requests = RequestToOrder::with('details')
            ->whereIn('status', ['pending'])
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

    public function list()
    {
        $requests = Request::with(['details', 'department', 'user'])
            ->where('status', 'to_order')
            ->get();

        return Inertia::render('Request/Order/ListToOrder', [
            'requests' => $requests
        ]);
    }

    public function store(HttpRequest $request)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit' => 'nullable|string|max:20',
            'items.*.item_description' => 'nullable|string|max:255',
        ]);

        DB::transaction(function () use ($validated) {
            $order = RequestToOrder::create([
                'user_id' => auth()->id(),
                'order_no' => $this->generateOrderNumber(),
                'order_date' => now(),
                'notes' => $validated['notes'],
                'status' => 'pending'
            ]);

            foreach ($validated['items'] as $item) {
                $order->details()->create([
                    'quantity' => $item['quantity'],
                    'unit' => $item['unit'] ?? null,
                    'item_description' => $item['item_description'] ?? null,
                ]);
            }
        });

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
        if ($order->status === 'forPO') {
            return back()->withErrors(['status' => 'Already approved']);
        }

        $order->update(['status' => 'forPO']);
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
        if ($order->status === 'forEOD') {
            return back()->withErrors(['status' => 'Already sent for EOD approval']);
        }

        $order->update(['status' => 'forEOD']);
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

