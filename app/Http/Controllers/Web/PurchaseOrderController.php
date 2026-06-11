<?php

namespace App\Http\Controllers\Web;

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
use App\Services\ActivityLogger;
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

        // Define valid statuses for each role
        $validStatuses = [];
        
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
                    // Invalid status parameter - return empty result
                    $query->whereRaw('1 = 0');
                }
            }
            // If no status parameter, show all
            
        } elseif ($user->role === 'property_custodian') {
            $validStatuses = ['approved'];
            
            if ($request->filled('status')) {
                if (in_array($status, $validStatuses)) {
                    $query->whereIn('status', ['approved', 'voucherCreated']);
                } else {
                    // Invalid status parameter - return empty result
                    $query->whereRaw('1 = 0');
                }
            } else {
                // Default show approved and voucherCreated
                $query->whereIn('status', ['approved', 'voucherCreated']);
            }
            
        } else {
            // Other roles: only show forEOD
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
                    ActivityLogger::make($request)
                        ->on($canvas)
                        ->log("Canvas status updated to submitted");
                }
            }

            // Activity log for PO
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