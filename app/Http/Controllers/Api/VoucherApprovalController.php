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

        return Inertia::render('Vouchers/Index', [
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
            // Update voucher status + audited_by field
            $voucher->update([
                'status' => $newStatus,
                'audited_by' => $user->id, // store who approved/rejected
            ]);

            // Create approval log in voucher_approvals table
            VoucherApproval::create([
                'voucher_id' => $voucher->id,
                'user_id' => $user->id,
                'status' => $newStatus,
                'remarks' => "Voucher: {$voucher->voucher_no} updated to {$newStatus}" 
                            . ($comment ? " | Comment: {$comment}" : ""),
                'approved_at' => now(),
            ]);

            // ===== NOTIFICATION BACK TO CREATOR =====
            // Only notify if approved
            if ($action === 'approve') {
                $creator = User::find($voucher->user_id);
                
                if ($creator) {
                    DB::table('notifications')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'type' => 'VoucherApproved',
                        'notifiable_type' => 'App\Models\User',
                        'notifiable_id' => $creator->id,
                        'data' => json_encode([
                            'voucher_id' => $voucher->id,
                            'title' => 'Voucher Approved',
                            'voucher_no' => $voucher->voucher_no,
                            'amount' => number_format($voucher->check_amount, 2),
                            'payee' => $voucher->payee,
                            'approved_by' => $user->name,
                            'status' => $newStatus,
                            'message' => "Your voucher #{$voucher->voucher_no} for {$voucher->payee} (₱" . number_format($voucher->check_amount, 2) . ") has been approved and is now ready for check processing",
                            'link' => route('vouchers.view', $voucher->id),
                            'comment' => $comment,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            // ===== END NOTIFICATIONS =====

            // Activity log with all details
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

    public function auditreturn($id, Request $request) 
    {
        $request->validate([
            'password' => 'required',
            'action' => 'required|in:return,reject',
            'comment' => 'nullable|string|max:500',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Incorrect password']);
        }

        $voucher = Voucher::findOrFail($id);

        if ($voucher->status === 'return') {
            return back()->withErrors(['status' => 'Voucher is already return for review']);
        }

        if ($voucher->status === 'rejected') {
            return back()->withErrors(['status' => 'Voucher is already rejected']);
        }

        $action = $request->input('action');
        $comment = $request->input('comment');
        $newStatus = $action === 'return' ? 'return' : 'rejected';
        $message = $action === 'return'
            ? 'Voucher return for review successfully'
            : 'Voucher rejected successfully';

        DB::transaction(function () use ($voucher, $newStatus, $user, $action, $comment) {
            // Update voucher status + audited_by field
            $voucher->update([
                'status' => $newStatus,
                'audited_by' => $user->id, // store who approved/rejected
            ]);

            // Create approval log in voucher_approvals table
            VoucherApproval::create([
                'voucher_id' => $voucher->id,
                'user_id' => $user->id,
                'status' => $newStatus,
                'remarks' => "Voucher: {$voucher->voucher_no} updated to {$newStatus}" 
                            . ($comment ? " | Comment: {$comment}" : ""),
                'approved_at' => now(),
            ]);

            // ===== NOTIFICATION BACK TO CREATOR =====
            $creator = User::find($voucher->user_id);
            
            if ($creator) {
                if ($action === 'return') {
                    // Notify for return
                    DB::table('notifications')->insert([
                        'id' => (string) \Illuminate\Support\Str::uuid(),
                        'type' => 'VoucherReturned',
                        'notifiable_type' => 'App\Models\User',
                        'notifiable_id' => $creator->id,
                        'data' => json_encode([
                            'voucher_id' => $voucher->id,
                            'title' => 'Voucher Returned for Review',
                            'voucher_no' => $voucher->voucher_no,
                            'amount' => number_format($voucher->check_amount, 2),
                            'payee' => $voucher->payee,
                            'returned_by' => $user->name,
                            'status' => $newStatus,
                            'message' => "Your voucher #{$voucher->voucher_no} for {$voucher->payee} (₱" . number_format($voucher->check_amount, 2) . ") has been returned for review" . ($comment ? ": {$comment}" : ""),
                            'link' => route('vouchers.view', $voucher->id),
                            'comment' => $comment,
                        ]),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
            // ===== END NOTIFICATIONS =====

            // Activity log with all details
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