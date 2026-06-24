<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Actions\Approvals\VerifyPassword;
use App\Models\Voucher;
use App\Models\User;
use App\Models\VoucherApproval;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;

class VoucherApprovalController extends Controller
{
    public function __construct(
        protected VerifyPassword $verifyPassword
    ) {}

    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Vouchers/Index', [
            'vouchers' => Voucher::with(['user', 'details'])
                                ->whereIn('status', ['forAudit'])
                                ->get(),
            'accounts' => \App\Models\Account::all(),
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
        $this->verifyPassword->execute($request->password, $user);

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

        DB::transaction(function () use ($voucher, $newStatus, $user, $action, $comment, $request) {
            $this->applyVoucherStatus($voucher, $newStatus, $user, $comment, $request);

            if ($action === 'approve') {
                $this->notifyVoucherCreator($voucher, $user, $comment, 'VoucherApproved', 'Voucher Approved');
            }
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
        $this->verifyPassword->execute($request->password, $user);

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

        DB::transaction(function () use ($voucher, $newStatus, $user, $action, $comment, $request) {
            $this->applyVoucherStatus($voucher, $newStatus, $user, $comment, $request);

            if ($action === 'return') {
                $this->notifyVoucherCreator($voucher, $user, $comment, 'VoucherReturned', 'Voucher Returned for Review');
            }
        });

        return back()->with([
            'success' => $message,
            'voucher' => $voucher->fresh()
        ]);
    }

    protected function applyVoucherStatus(Voucher $voucher, string $newStatus, User $user, ?string $comment, Request $request): void
    {
        $voucher->update([
            'status' => $newStatus,
            'audited_by' => $user->id,
        ]);

        VoucherApproval::create([
            'voucher_id' => $voucher->id,
            'user_id' => $user->id,
            'status' => $newStatus,
            'remarks' => "Voucher: {$voucher->voucher_no} updated to {$newStatus}"
                . ($comment ? " | Comment: {$comment}" : ""),
            'approved_at' => now(),
        ]);

        $logMessage = "Voucher {$voucher->voucher_no} updated to {$newStatus} by {$user->name}"
            . ($comment ? " | Comment: {$comment}" : "");

        ActivityLogger::make($request)
            ->on($voucher)
            ->by($user)
            ->with([
                'voucher_no' => $voucher->voucher_no,
                'new_status' => $newStatus,
                'comment' => $comment,
                'audited_by' => $user->id,
            ])
            ->logName('Voucher Update')
            ->log($logMessage);
    }

    protected function notifyVoucherCreator(Voucher $voucher, User $user, ?string $comment, string $type, string $title): void
    {
        $creator = User::find($voucher->user_id);
        if (!$creator) {
            return;
        }

        DB::table('notifications')->insert([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'notifiable_type' => 'App\Models\User',
            'notifiable_id' => $creator->id,
            'data' => json_encode([
                'voucher_id' => $voucher->id,
                'title' => $title,
                'voucher_no' => $voucher->voucher_no,
                'amount' => number_format($voucher->check_amount, 2),
                'payee' => $voucher->payee,
                'approved_by' => $user->name,
                'status' => $voucher->status,
                'message' => $this->buildNotificationMessage($voucher, $user, $type, $comment),
                'link' => route('vouchers.view', $voucher->id),
                'comment' => $comment,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    protected function buildNotificationMessage(Voucher $voucher, User $user, string $type, ?string $comment): string
    {
        $amount = "₱" . number_format($voucher->check_amount, 2);

        return match ($type) {
            'VoucherApproved' => "Your voucher #{$voucher->voucher_no} for {$voucher->payee} ({$amount}) has been approved and is now ready for check processing",
            'VoucherReturned' => "Your voucher #{$voucher->voucher_no} for {$voucher->payee} ({$amount}) has been returned for review" . ($comment ? ": {$comment}" : ""),
            default => "Voucher #{$voucher->voucher_no} status updated to {$voucher->status}",
        };
    }
}