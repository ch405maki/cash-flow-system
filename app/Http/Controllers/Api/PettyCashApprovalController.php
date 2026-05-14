<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use App\Models\PettyCash;
use App\Models\PettyCashApproval;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PettyCashApprovalController extends Controller
{
    public function auditApproval(Request $request, PettyCash $pettyCash)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        // Update petty cash status
        $pettyCash->update([
            'status' => 'audited',
        ]);

        PettyCashApproval::create([
            'petty_cash_id' => $pettyCash->id,
            'user_id'       => Auth::id(),
            'status'        => 'audited',
            'remarks'       => $request->input('remarks', ''),
            'approved_at'   => now(),
        ]);

        ActivityLogger::make($request)
            ->on($pettyCash)
            ->log("Petty cash voucher #{$pettyCash->pcv_no} audited and approved");

        return back()->with('success', 'Approval saved successfully!');
    }

    public function auditReturn(Request $request, PettyCash $pettyCash)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        // Update petty cash status
        $pettyCash->update([
            'status' => 'returned',
        ]);

        PettyCashApproval::create([
            'petty_cash_id' => $pettyCash->id,
            'user_id'       => Auth::id(),
            'status'        => 'returned',
            'remarks'       => $request->input('remarks', ''),
            'approved_at'   => now(),
        ]);

        ActivityLogger::make($request)
            ->on($pettyCash)
            ->log("Petty cash voucher #{$pettyCash->pcv_no} returned by audit");

        return back()->with('success', 'Approval saved successfully!');
    }

    public function auditApprovalLiquidate(Request $request, PettyCash $pettyCash)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        // Update petty cash status
        $pettyCash->update([
            'status' => 'approved liquidation',
        ]);

        PettyCashApproval::create([
            'petty_cash_id' => $pettyCash->id,
            'user_id'       => Auth::id(),
            'status'        => 'approved liquidation',
            'remarks'       => $request->input('remarks', ''),
            'approved_at'   => now(),
        ]);

        ActivityLogger::make($request)
            ->on($pettyCash)
            ->log("Petty cash liquidation approved for voucher #{$pettyCash->pcv_no}");

        return back()->with('success', 'Approval saved successfully!');
    }

    public function auditRemarks(Request $request, PettyCash $pettyCash)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        // Update petty cash status
        $pettyCash->update([
            'status' => 'audited',
        ]);

        PettyCashApproval::create([
            'petty_cash_id' => $pettyCash->id,
            'user_id'       => Auth::id(),
            'status'        => 'audited',
            'remarks'       => $request->input('remarks', ''),
            'approved_at'   => now(),
        ]);

        ActivityLogger::make($request)
            ->on($pettyCash)
            ->log("Petty cash voucher #{$pettyCash->pcv_no} audit remarks added");

        return back()->with('success', 'Approval saved successfully!');
    }
}
