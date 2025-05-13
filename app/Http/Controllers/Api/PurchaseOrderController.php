<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PurchaseOrderController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        DB::beginTransaction();

        try {
            $validated = $request->validate([
                'po_no' => 'required|string|unique:purchase_orders',
                'payee' => 'required|string|max:255',
                'check_payable_to' => 'required|string|max:255',
                'date' => 'required|date',
                'amount' => 'required|numeric|min:0',
                'purpose' => 'required|string|max:500',
                'status' => 'required|in:draft,approved,rejected,completed',
                'remarks' => 'nullable|string',
                'user_id' => 'required|exists:users,id',
                'department_id' => 'required|exists:departments,id',
                'account_id' => 'required|exists:accounts,id',
                'items' => 'required|array|min:1',
                'items.*.quantity' => 'required|numeric|min:1',
                'items.*.unit' => 'required|string|max:20',
                'items.*.item_description' => 'required|string|max:255',
                'items.*.unit_price' => 'required|numeric|min:0',
                'items.*.amount' => 'required|numeric|min:0'
            ]);

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create($validated);

            // Create purchase order details
            foreach ($validated['items'] as $item) {
                $purchaseOrder->details()->create($item);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order created successfully',
                'data' => $purchaseOrder->load(['user', 'department', 'account', 'details']),
                'meta' => [
                    'items_count' => count($validated['items']),
                    'total_amount' => $purchaseOrder->amount,
                    'created_at' => now()->toDateTimeString()
                ]
            ], 201);

        } catch (ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
                'meta' => [
                    'valid_statuses' => ['draft', 'approved', 'rejected', 'completed']
                ]
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create purchase order',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
}