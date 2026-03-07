<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

use App\Models\PurchaseOrder;
use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Account;
use App\Models\Signatory;
use App\Models\User;
use App\Models\VoucherApproval;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Database\QueryException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use App\Models\DistributionExpense;
use App\Models\PettyCash;


class VoucherController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        return Inertia::render('Vouchers/Index', [
            'vouchers' => Voucher::with(['user', 'details'])
                        ->whereIn('status', ['draft', 'return', 'rejected', 'unreleased', 'released', 'completed'])
                        ->get(),
            'accounts' => Account::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'access' => $user->access_id,
            ]
        ]);
    }

    public function create(Request $request)
    {
        $voucherNumber = $this->generateVoucherNumber();
        $poId = $request->query('po_id');
        $pettyCashId = $request->query('petty_cash_id');
        
        $data = [
            'accounts' => Account::orderBy('account_title')->get(),
            'voucher_number' => $voucherNumber,
        ];
        
        // Handle purchase order data
        if ($poId) {
            $purchaseOrder = PurchaseOrder::with(['department', 'account'])->find($poId);
            if ($purchaseOrder) {
                $data['purchase_order'] = $purchaseOrder;
            }
        }
        
        // Handle distribution expenses from petty cash
        if ($pettyCashId) {
            $pettyCash = PettyCash::with('distributionExpenses')->find($pettyCashId);
            
            if ($pettyCash) {
                $data['petty_cash'] = $pettyCash;
                $data['distribution_expenses'] = $pettyCash->distributionExpenses;
                
                // You can also pre-fill other voucher data from petty cash
                $data['prefilled_data'] = [
                    'paid_to' => $pettyCash->paid_to,
                    'total_amount' => $pettyCash->distributionExpenses->sum('amount'),
                    'remarks' => "Voucher created from Petty Cash #{$pettyCash->pcv_no}",
                    // Add any other fields you want to pre-fill
                ];
            }
        }
        
        return Inertia::render('Vouchers/Create', $data);
    }

    protected function generateVoucherNumber(): string
    {
        $prefix = 'V-' . now()->format('Ym') . '-'; 
        $lastVoucher = Voucher::where('voucher_no', 'like', $prefix . '%')
            ->orderBy('voucher_no', 'desc')
            ->first();

        $sequence = $lastVoucher
            ? (int) str_replace($prefix, '', $lastVoucher->voucher_no) + 1
            : 1;

        return $prefix . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    public function downloadReceipt(Voucher $voucher)
    {
        if (!$voucher->receipt) {
            abort(404, 'No receipt found.');
        }

        $filePath = $voucher->receipt;
        if (!Storage::disk('public')->exists($filePath)) {
            abort(404, 'File not found on disk.');
        }

        $filename = 'receipt_' . $voucher->voucher_no . '.' . pathinfo($filePath, PATHINFO_EXTENSION);

        return Storage::disk('public')->download($filePath, $filename);
    }

    public function edit(Voucher $voucher)
    {
        return Inertia::render('Vouchers/Edit', [
            'voucher' => $voucher->load('details.account'),
            'accounts' => Account::orderBy('account_title')->get()
        ]);
    }

    public function view(Voucher $voucher)
    {
        $voucher->load(['user', 'auditor', 'details.account', 'approvals.user']);

        return Inertia::render('Vouchers/View', [ 
            'voucher' => $voucher,
            'accounts' => Account::all(),
            'authUser' => [ 
                'id' => Auth::user()->id,
                'name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'access_id' => Auth::user()->access_id,
                'role' => Auth::user()->role,
            ],
            'signatories' => Signatory::all(),
        ]);
    }

    public function viewWithPO($po_id)
    {
        $voucher = Voucher::with(['user', 'details.account', 'approvals.user'])->where('po_id', $po_id)->firstOrFail();

        return Inertia::render('Vouchers/View', [ 
            'voucher' => $voucher,
            'accounts' => Account::all(),
            'authUser' => [ 
                'id' => Auth::id(),
                'name' => Auth::user()->first_name . ' ' . Auth::user()->last_name,
                'access_id' => Auth::user()->access_id,
                'role' => Auth::user()->role,
            ],
            'signatories' => Signatory::all(),
        ]);
    }

    public function forDirector($id, Request $request)
    {
        $request->validate([
            'password' => 'required',
            'action' => 'required|in:forAudit,reject,released'
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $voucher = Voucher::findOrFail($id);

        if ($voucher->status === 'forAudit') {
            return back()->withErrors(['status' => 'Voucher is already sent']);
        }

        $action = $request->input('action');
        $newStatus = match ($action) {
            'forAudit' => 'forAudit',
            'released' => 'released',
            default => 'rejected',
        };

        $message = match ($action) {
            'forAudit' => 'Voucher sent to Executive Director',
            'released' => 'Voucher marked as released',
            default => 'Voucher rejected successfully',
        };

        DB::transaction(function () use ($voucher, $newStatus, $user, $action) {
            $voucher->update(['status' => $newStatus]);

            // Update the approval entry for this user if it exists
            VoucherApproval::create([
                'voucher_id' => $voucher->id,
                'user_id' => $user->id,
                'status' => $newStatus,
                'remarks' => "Voucher: {$voucher->voucher_no} Update as {$newStatus}",
                'approved_at' => now(),
            ]);

            // Log the activity
            activity()
                ->performedOn($voucher)
                ->causedBy($user)
                ->useLog('Voucher Update')
                ->withProperties([
                    'voucher_no' => $voucher->voucher_no,
                    'action' => $action,
                    'new_status' => $newStatus,
                ])
                ->log("Voucher {$voucher->voucher_no} Updated: {$action} ");
        });

        return back()->with([
            'success' => $message,
            'voucher' => $voucher->fresh()
        ]);
    }

    /**
     * Standard response format
     */
    protected function successResponse(
        string $message,
        mixed $data = null,
        int $status = 200,
        array $meta = []
    ): JsonResponse {
        $response = [
            'success' => true,
            'message' => $message,
            'data' => $data,
        ];

        if (!empty($meta)) {
            $response['meta'] = $meta;
        }

        return response()->json($response, $status);
    }

    protected function errorResponse(
        string $message,
        mixed $errors = null,
        int $status = 500
    ): JsonResponse {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ], $status);
    }
}
