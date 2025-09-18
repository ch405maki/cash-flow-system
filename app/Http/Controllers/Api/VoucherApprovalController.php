<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Account;
use App\Models\Signatory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\PurchaseOrder;
use App\Models\VoucherApproval;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

use Illuminate\Database\QueryException;
use Inertia\Inertia;

class VoucherApprovalController extends Controller {
    
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Vouchers/ForApproval/Index', [
            'vouchers' => Voucher::with(['user', 'details'])
                                ->whereIn('status', ['forAudit'])
                                ->get(),
            'accounts' => Account::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'access' => $user->access_id,
            ]
        ]);
    }

    public function auditreview($id, Request $request) 
    {
        $request->validate([
            'password' => 'required',
            'action' => 'required|in:approve,reject',
            'comment' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $voucher = Voucher::findOrFail($id);

        if ($voucher->status === 'forCheck') {
            return back()->withErrors(['status' => 'Voucher is already approved for check releasing']);
        }

        if ($voucher->status === 'rejected') {
            return back()->withErrors(['status' => 'Voucher is already rejected']);
        }

        $action = $request->input('action');
        $comment = $request->input('comment');
        $newStatus = $action === 'approve' ? 'forCheck' : 'rejected';
        $message = $action === 'approve'
            ? 'Voucher approved successfully'
            : 'Voucher rejected successfully';

        DB::transaction(function () use ($voucher, $newStatus, $user, $action, $comment) {
            // ✅ Update voucher status + audited_by field
            $voucher->update([
                'status' => $newStatus,
                'audited_by' => $user->id, // store who approved/rejected
            ]);

            // ✅ Create approval log in voucher_approvals table
            VoucherApproval::create([
                'voucher_id' => $voucher->id,
                'user_id' => $user->id,
                'status' => $newStatus,
                'remarks' => "Voucher: {$voucher->voucher_no} updated to {$newStatus}" 
                            . ($comment ? " | Comment: {$comment}" : ""),
                'approved_at' => now(),
            ]);

            // ✅ Activity log with all details
            $logMessage = "Voucher {$voucher->voucher_no} updated to {$newStatus} by {$user->name}";
            if ($comment) {
                $logMessage .= " | Comment: {$comment}";
            }

            activity()
                ->performedOn($voucher)
                ->causedBy($user)
                ->useLog('Voucher Update')
                ->withProperties([
                    'voucher_no' => $voucher->voucher_no,
                    'action' => $action,
                    'new_status' => $newStatus,
                    'comment' => $comment,
                    'audited_by' => $user->id,
                ])
                ->log($logMessage);
        });

        return back()->with([
            'success' => $message,
            'voucher' => $voucher->fresh()
        ]);
    }

}