<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use App\Services\ApprovalService;
use Illuminate\Http\Request;
use App\Models\PettyCash;
use App\Models\PettyCashApproval;
use Illuminate\Support\Facades\Auth;

class PettyCashApprovalController extends Controller
{
    public function __construct(
        protected ApprovalService $approvalService
    ) {}

    public function auditApproval(Request $request, PettyCash $pettyCash)
    {
        $request->validate(['remarks' => 'nullable|string|max:500']);

        $this->applyPettyCashStatus($pettyCash, 'audited', $request);

        return back()->with('success', 'Approval saved successfully!');
    }

    public function auditReturn(Request $request, PettyCash $pettyCash)
    {
        $request->validate(['remarks' => 'nullable|string|max:500']);

        $this->applyPettyCashStatus($pettyCash, 'returned', $request);

        return back()->with('success', 'Approval saved successfully!');
    }

    public function auditApprovalLiquidate(Request $request, PettyCash $pettyCash)
    {
        $request->validate(['remarks' => 'nullable|string|max:500']);

        $this->applyPettyCashStatus($pettyCash, 'approved liquidation', $request, 'liquidation');

        return back()->with('success', 'Approval saved successfully!');
    }

    public function auditRemarks(Request $request, PettyCash $pettyCash)
    {
        $request->validate(['remarks' => 'nullable|string|max:500']);

        $this->applyPettyCashStatus($pettyCash, 'audited', $request, 'remarks');

        return back()->with('success', 'Approval saved successfully!');
    }

    protected function applyPettyCashStatus(PettyCash $pettyCash, string $status, Request $request, ?string $type = null): void
    {
        $user = Auth::user();

        $pettyCash->update(['status' => $status]);

        PettyCashApproval::create([
            'petty_cash_id' => $pettyCash->id,
            'user_id'       => $user->id,
            'status'        => $status,
            'remarks'       => $request->input('remarks', ''),
            'approved_at'   => now(),
        ]);

        $logMessage = match ($type) {
            'liquidation' => "Petty cash liquidation approved for voucher #{$pettyCash->pcv_no}",
            'remarks'     => "Petty cash voucher #{$pettyCash->pcv_no} audit remarks added",
            default       => "Petty cash voucher #{$pettyCash->pcv_no} {$status}",
        };

        ActivityLogger::make($request)
            ->on($pettyCash)
            ->log($logMessage);
    }
}
