<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Account;
use App\Models\Department;
use App\Models\Canvas;
use App\Models\CanvasFile;
use App\Models\CanvasSelectedFile;
use App\Models\PurchaseOrderApproval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Services\ActivityLogger;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function indexData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $status = $request->query('status');

        $query = PurchaseOrder::with(['user', 'department', 'account', 'details'])
                    ->latest();

        if ($user->role === 'purchasing') {
            $validStatuses = ['approved', 'draft', 'forEOD', 'rejected'];

            if ($request->filled('status')) {
                if (in_array($status, $validStatuses)) {
                    if ($status === 'approved') {
                        $query->whereIn('status', ['approved', 'voucherCreated']);
                    } else {
                        $query->where('status', $status);
                    }
                } else {
                    $query->whereRaw('1 = 0');
                }
            }

        } elseif ($user->role === 'property_custodian') {
            if ($request->filled('status')) {
                if ($status === 'approved') {
                    $query->whereIn('status', ['approved', 'voucherCreated']);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $query->whereIn('status', ['approved', 'voucherCreated']);
            }

        } else {
            $query->where('status', 'forEOD');
        }

        $purchaseOrders = $query->paginate(10);

        return response()->json([
            'success' => true,
            'data' => [
                'purchaseOrders' => $purchaseOrders,
            ],
        ]);
    }

    public function showData(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $user = Auth::user();

        $purchaseOrder->load([
            'user',
            'department',
            'account',
            'details',
            'canvas.selected_files.file',
            'canvas.selected_files.approval',
            'canvas.approvals.user',
            'approvals.user',
            'voucher.user',
            'voucher.details',
            'voucher.approvals.user',
        ]);

        $firstFileId = $purchaseOrder->canvas?->selected_files?->first()?->canvas_file_id;

        $signatories = \App\Models\User::whereIn('role', ['executive_director', 'president', 'vice_president'])
            ->get(['first_name', 'middle_name', 'last_name', 'role', 'id'])
            ->map(fn ($u) => [
                'full_name' => trim($u->first_name . ' ' . ($u->middle_name ?? '') . ' ' . $u->last_name),
                'position' => $u->role,
            ]);

        return response()->json([
            'success' => true,
            'data' => [
                'purchaseOrder' => $purchaseOrder,
                'authUser' => [
                    'id' => $user->id,
                    'name' => $user->first_name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'access' => $user->access_id,
                ],
                'firstFileId' => $firstFileId,
                'signatories' => $signatories,
            ],
        ]);
    }

    public function createData(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => Auth::id(),
                'departments' => Department::orderBy('department_name')->get(['id', 'department_name']),
                'accounts' => Account::orderBy('account_title')->get(['id', 'account_title']),
            ],
        ]);
    }

    public function editData(PurchaseOrder $purchaseOrder): JsonResponse
    {
        $purchaseOrder->load(['details']);

        return response()->json([
            'success' => true,
            'data' => [
                'purchaseOrder' => $purchaseOrder,
                'departments' => Department::orderBy('department_name')->get(['id', 'department_name']),
                'accounts' => Account::orderBy('account_title')->get(['id', 'account_title']),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payee' => 'required|string',
            'check_payable_to' => 'required|string',
            'date' => 'required|date',
            'purpose' => 'required|string',
            'tin_no' => 'nullable|string',
            'status' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'account_id' => 'nullable|exists:accounts,id',
            'details' => 'required|array|min:1',
            'details.*.quantity' => 'required|numeric|min:0',
            'details.*.unit' => 'required|string',
            'details.*.item_description' => 'required|string',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.amount' => 'required|numeric|min:0',
            'tagging' => 'required|in:with_canvas,no_canvas',
            'canvas_id' => [
                'nullable',
                'exists:canvases,id',
                Rule::requiredIf(fn () => $request->tagging === 'with_canvas')
            ],
            'file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx|max:2048',
        ]);

        if ($validated['tagging'] === 'with_canvas' && empty($validated['canvas_id'])) {
            return response()->json([
                'message' => 'Canvas ID is required when tagging is "with_canvas"'
            ], 422);
        }

        $validated['amount'] = collect($validated['details'])->sum('amount');
        $validated['account_id'] = 1;

        DB::beginTransaction();
        try {
            $yearMonth = date('Ym');
            $lastPO = PurchaseOrder::where('po_no', 'like', "PO-{$yearMonth}%")
                ->lockForUpdate()
                ->orderBy('po_no', 'desc')
                ->first();

            $sequence = $lastPO ? ((int) substr($lastPO->po_no, -4)) + 1 : 1;
            $validated['po_no'] = 'PO-' . $yearMonth . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            $purchaseOrder = PurchaseOrder::create($validated);

            foreach ($validated['details'] as $detail) {
                $purchaseOrder->details()->create($detail);
            }

            if ($validated['tagging'] === 'no_canvas' && $request->hasFile('file')) {
                $canvas = Canvas::create([
                    'title' => $purchaseOrder->po_no,
                    'description' => 'Automatically created for ' . $purchaseOrder->po_no,
                    'status' => 'pending',
                    'note' => 'Supporting Document',
                    'created_by' => $validated['user_id'],
                ]);

                $file = $request->file('file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('canvases', $filename, 'public');

                $canvasFile = CanvasFile::create([
                    'canvas_id' => $canvas->id,
                    'file_path' => $filename,
                    'original_filename' => $file->getClientOriginalName(),
                    'type' => $file->getClientMimeType(),
                ]);

                CanvasSelectedFile::create([
                    'canvas_id' => $canvas->id,
                    'canvas_file_id' => $canvasFile->id,
                ]);

                $purchaseOrder->update(['canvas_id' => $canvas->id]);

                ActivityLogger::make($request)
                    ->on($canvas)
                    ->with([
                        'file_name' => $file->getClientOriginalName(),
                        'po_id' => $purchaseOrder->id,
                    ])
                    ->log("Supporting document uploaded for PO {$purchaseOrder->po_no}");
            }

            if ($validated['tagging'] === 'with_canvas' && $validated['canvas_id']) {
                $canvas = Canvas::find($validated['canvas_id']);
                if ($canvas) {
                    $canvas->update([
                        'status' => 'poCreated',
                        'purchase_order_id' => $purchaseOrder->id,
                    ]);

                    ActivityLogger::make($request)
                        ->on($canvas)
                        ->with([
                            'linked_po' => $purchaseOrder->po_no,
                            'canvas_id' => $canvas->id,
                            'approval_id' => 1,
                        ])
                        ->log("Canvas tagged to {$purchaseOrder->po_no}");
                }
            }

            ActivityLogger::make($request)
                ->on($purchaseOrder)
                ->with([
                    'po_no' => $purchaseOrder->po_no,
                    'amount' => $validated['amount'],
                    'department_id' => $validated['department_id'],
                ])
                ->logName('PO Created')
                ->log('Created purchase order');

            PurchaseOrderApproval::create([
                'purchase_order_id' => $purchaseOrder->id,
                'user_id' => $validated['user_id'],
                'status' => 'pending',
                'remarks' => "Purchase order created # {$purchaseOrder->po_no}",
            ]);

            DB::commit();
            return response()->json($purchaseOrder->load('details', 'canvas.files'), 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to create purchase order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft purchase orders can be edited.'
            ], 422);
        }

        $validated = $request->validate([
            'payee' => 'required|string',
            'check_payable_to' => 'required|string',
            'date' => 'required|date',
            'purpose' => 'required|string',
            'tin_no' => 'nullable|string',
            'department_id' => 'required|exists:departments,id',
            'details' => 'required|array|min:1',
            'details.*.quantity' => 'required|numeric|min:0',
            'details.*.unit' => 'required|string',
            'details.*.item_description' => 'required|string',
            'details.*.unit_price' => 'required|numeric|min:0',
            'details.*.amount' => 'required|numeric|min:0',
        ]);

        $validated['amount'] = collect($validated['details'])->sum('amount');

        DB::beginTransaction();
        try {
            $purchaseOrder->update([
                'payee' => $validated['payee'],
                'check_payable_to' => $validated['check_payable_to'],
                'date' => $validated['date'],
                'purpose' => $validated['purpose'],
                'tin_no' => $validated['tin_no'] ?? null,
                'department_id' => $validated['department_id'],
                'amount' => $validated['amount'],
            ]);

            $purchaseOrder->details()->delete();

            foreach ($validated['details'] as $detail) {
                $purchaseOrder->details()->create($detail);
            }

            ActivityLogger::make($request)
                ->on($purchaseOrder)
                ->with([
                    'po_no' => $purchaseOrder->po_no,
                    'amount' => $validated['amount'],
                    'department_id' => $validated['department_id'],
                ])
                ->logName('PO Updated')
                ->log('Updated draft purchase order');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Purchase order updated successfully',
                'data' => $purchaseOrder->fresh()->load('details'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'message' => 'Failed to update purchase order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,released,to_order,forEOD',
            'password' => 'required|string',
            'remarks' => 'nullable|string|max:500',
        ]);

        if (!Hash::check($validated['password'], auth()->user()->password)) {
            return response()->json(['message' => 'Incorrect password'], 422);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $purchaseOrder->status;

            $purchaseOrder->update([
                'status' => $validated['status'],
                'remarks' => $validated['remarks'] ?? null
            ]);

            if ($purchaseOrder->canvas_id) {
                $canvas = Canvas::find($purchaseOrder->canvas_id);
                if ($canvas) {
                    $canvas->update(['status' => 'submitted']);

                    ActivityLogger::make($request)
                        ->on($canvas)
                        ->log("Canvas status updated to submitted");
                }
            }

            ActivityLogger::make($request)
                ->on($purchaseOrder)
                ->with([
                    'po_no' => $purchaseOrder->po_no,
                    'old_status' => $oldStatus,
                    'new_status' => $validated['status'],
                    'remarks' => $validated['remarks'] ?? null,
                ])
                ->logName('Approval')
                ->log("Status changed to {$validated['status']}");

            PurchaseOrderApproval::create([
                'purchase_order_id' => $purchaseOrder->id,
                'user_id' => auth()->id(),
                'status' => $validated['status'],
                'remarks' => "PO #{$purchaseOrder->po_no} updated to {$validated['status']}",
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Status updated successfully',
                'purchaseOrder' => $purchaseOrder->fresh()->load('approvals.user'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to update status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(PurchaseOrder $purchaseOrder): JsonResponse
    {
        if ($purchaseOrder->status !== 'draft') {
            return response()->json([
                'message' => 'Only draft purchase orders can be deleted.'
            ], 422);
        }

        DB::beginTransaction();
        try {
            $poNo = $purchaseOrder->po_no;

            $purchaseOrder->approvals()->delete();
            $purchaseOrder->details()->delete();
            $purchaseOrder->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Purchase order {$poNo} deleted successfully.",
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Failed to delete purchase order',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
