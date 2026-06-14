<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Account;
use App\Models\Department;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->query('status');

        $query = PurchaseOrder::with(['user', 'department', 'account', 'details'])
                    ->latest();

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
                    $query->whereRaw('1 = 0');
                }
            }

        } elseif ($user->role === 'property_custodian') {
            $validStatuses = ['approved'];

            if ($request->filled('status')) {
                if (in_array($status, $validStatuses)) {
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
}
