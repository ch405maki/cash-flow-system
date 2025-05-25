<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Account;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Request as PurchaseRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['user', 'department', 'account', 'details'])
            ->latest()
            ->paginate(10);

        return Inertia::render('PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    public function show(PurchaseOrder $purchaseOrder)
    {

        $user = Auth::user();

        return Inertia::render('PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder->load([
                'user',
                'department', 
                'account',
                'details'
            ]),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'access' => $user->access_id,
            ],
        ]);
    }

    public function create(PurchaseRequest $request)
    {
        return Inertia::render('PurchaseOrders/Create', [
            'user_id' => auth()->id(),
            'departments' => Department::orderBy('department_name')->get(['id', 'department_name']),
            'accounts' => Account::orderBy('account_title')->get(['id', 'account_title']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            // Removed po_no from validation
            'payee' => 'required|string',
            'check_payable_to' => 'required|string',
            'date' => 'required|date',
            'purpose' => 'required|string',
            'status' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'account_id' => 'required|exists:accounts,id',
            'details' => 'required|array',
            'details.*.quantity' => 'required|numeric|min:1',
            'details.*.unit' => 'required|string',
            'details.*.item_description' => 'required|string',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.amount' => 'required|numeric|min:0',
        ]);

        // Generate PO number directly in controller
        $yearMonth = date('Ym');
        $lastPO = PurchaseOrder::where('po_no', 'like', "PO{$yearMonth}%")
            ->orderBy('po_no', 'desc')
            ->first();

        if ($lastPO) {
            $sequence = (int) substr($lastPO->po_no, -4);
            $sequence++;
        } else {
            $sequence = 1;
        }

        $validated['po_no'] = 'PO' . $yearMonth . str_pad($sequence, 4, '0', STR_PAD_LEFT);

        // Calculate total amount from details
        $validated['amount'] = collect($validated['details'])->sum('amount');

        DB::beginTransaction();
        try {
            $purchaseOrder = PurchaseOrder::create($validated);

            foreach ($validated['details'] as $detail) {
                $purchaseOrder->details()->create($detail);
            }

            DB::commit();

            return response()->json($purchaseOrder->load('details'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,released,to_order,to_property',
            'password' => 'required|string',
            'remarks' => 'nullable|string|max:500'
        ]);

        // Verify password
        if (!Hash::check($validated['password'], auth()->user()->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        DB::beginTransaction();
        try {
            $purchaseOrder->update([
                'status' => $validated['status'],
                'remarks' => $validated['remarks'] ?? null
            ]);

            // Log this status change
            DB::commit();

            return redirect()->back()->with('success', 'Status updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }
}