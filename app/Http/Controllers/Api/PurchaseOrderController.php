<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use App\Models\Account;
use App\Models\Canvas;
use App\Models\CanvasFile;
use App\Models\CanvasSelectedFile;
use App\Models\PurchaseOrderApproval;
use App\Models\Department;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Request as PurchaseRequest;
use Inertia\Inertia;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status');

        $query = PurchaseOrder::with(['user', 'department', 'account', 'details'])
                    ->latest();

        if ($user->role === 'purchasing') {
            // Purchasing can see all statuses, but filter if specific status requested
            if ($status) {
                if ($status === 'approved') {
                    // Include both approved and voucherCreated
                    $query->whereIn('status', ['approved', 'voucherCreated']);
                } elseif (in_array($status, ['draft', 'forEOD', 'rejected'])) {
                    $query->where('status', $status);
                }
            }
        } else {
            // Non-purchasing users can only see 'forEOD' status
            $query->where('status', 'forEOD');
        }

        $purchaseOrders = $query->paginate(10);

        return Inertia::render('PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders,
            'filters' => $request->only(['status']),
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
                'details',
                'canvas.selected_files.file',
                'canvas.selected_files.approval',
                'canvas.approvals.user',
                'approvals.user',
                'voucher.user',  
                'voucher.details',
                'voucher.approvals.user',
            ]),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'access' => $user->access_id,
                'name' => $user->first_name,
            ],
        ]);
    }


    public function create(Request $request)
    {
        $canvasId = $request->query('canvas_id');
        return Inertia::render('PurchaseOrders/Create', [
            'user_id' => Auth::id(),
            'departments' => Department::orderBy('department_name')->get(['id', 'department_name']),
            'accounts' => Account::orderBy('account_title')->get(['id', 'account_title']),
            'canvas_id' => $canvasId,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'payee' => 'required|string',
            'check_payable_to' => 'required|string',
            'date' => 'required|date',
            'purpose' => 'required|string',
            'status' => 'required|string',
            'user_id' => 'required|exists:users,id',
            'department_id' => 'required|exists:departments,id',
            'account_id' => 'nullable|exists:accounts,id',
            'details' => 'required|array|min:1',    
            'details.*.quantity' => 'required|numeric|min:1',
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
            // Generate PO number safely with lock
            $yearMonth = date('Ym');
            $lastPO = PurchaseOrder::where('po_no', 'like', "PO-{$yearMonth}%")
                ->lockForUpdate()
                ->orderBy('po_no', 'desc')
                ->first();

            $sequence = $lastPO ? ((int) substr($lastPO->po_no, -4)) + 1 : 1;
            $validated['po_no'] = 'PO-' . $yearMonth . str_pad($sequence, 4, '0', STR_PAD_LEFT);

            // Create purchase order
            $purchaseOrder = PurchaseOrder::create($validated);

            // Add details
            foreach ($validated['details'] as $detail) {
                $purchaseOrder->details()->create($detail);
            }

            // Handle file upload for no_canvas tagging
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
                    'approval_id' => 1,
                ]);

                $purchaseOrder->update(['canvas_id' => $canvas->id]);

                activity()
                    ->performedOn($canvas)
                    ->causedBy(auth()->user())
                    ->withProperties([
                        'file_name' => $file->getClientOriginalName(),
                        'po_id' => $purchaseOrder->id,
                    ])
                    ->log("Supporting document uploaded for PO {$purchaseOrder->po_no}");
            }

            // Optional canvas update for with_canvas tagging
            if ($validated['tagging'] === 'with_canvas' && $validated['canvas_id']) {
                $canvas = Canvas::find($validated['canvas_id']);
                if ($canvas) {
                    $canvas->update([
                        'status' => 'poCreated',
                        'purchase_order_id' => $purchaseOrder->id,
                    ]);

                    activity()
                        ->performedOn($canvas)
                        ->causedBy(auth()->user())
                        ->withProperties([
                            'linked_po' => $purchaseOrder->po_no,
                            'canvas_id' => $canvas->id,
                            'approval_id' => 1,
                        ])
                        ->log("Canvas tagged to {$purchaseOrder->po_no}");
                }
            }

            // Activity log for PO
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(auth()->user())
                ->useLog('PO Created')
                ->withProperties([
                    'po_no' => $purchaseOrder->po_no,
                    'amount' => $validated['amount'],
                    'department_id' => $validated['department_id'],
                ])
                ->log('Created purchase order');

            // Approval entry creation
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

    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'status' => 'required|in:approved,rejected,released,to_order,forEOD',
            'password' => 'required|string',
            'remarks' => 'nullable|string|max:500',
            'canvas_id' => 'nullable|exists:canvases,id' // Add validation for canvas_id
        ]);

        // Verify password
        if (!Hash::check($validated['password'], auth()->user()->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        DB::beginTransaction();
        try {
            $oldStatus = $purchaseOrder->status;

            // Update Purchase Order
            $purchaseOrder->update([
                'status' => $validated['status'],
                'remarks' => $validated['remarks'] ?? null
            ]);

            // Update related Canvas if canvas_id exists and status is being approved
            if ($purchaseOrder->canvas_id) {
                $canvas = Canvas::find($purchaseOrder->canvas_id);
                if ($canvas) {
                    $canvas->update([
                        'status' => 'submitted',
                    ]);
                    
                    // Log canvas update
                    activity()
                        ->performedOn($canvas)
                        ->causedBy(auth()->user())
                        ->log("Canvas status updated to submitted");
                }
            }

            // Activity log for PO
            activity()
                ->performedOn($purchaseOrder)
                ->causedBy(auth()->user())
                ->useLog('Approval')
                ->withProperties([
                    'po_no' => $purchaseOrder->po_no,
                    'old_status' => $oldStatus,
                    'new_status' => $validated['status'],
                    'remarks' => $validated['remarks'] ?? null,
                ])
                ->log("Status changed to {$validated['status']}");

            // Approval entry creation
            PurchaseOrderApproval::create([
                'purchase_order_id' => $purchaseOrder->id,
                'user_id' => auth()->id(),
                'status' => $validated['status'],
                'remarks' => "PO #{$purchaseOrder->po_no} updated to {$validated['status']}",
            ]);

            DB::commit();

            return redirect()->back()->with('success', 'Status updated successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update status: ' . $e->getMessage());
        }
    }
}