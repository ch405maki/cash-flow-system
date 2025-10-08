<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PettyCash;
use App\Models\PettyCashApproval;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PettyCashApprovalController extends Controller
{
    public function executive(){
        $pettyCash = PettyCash::with('items')
        ->whereIn('status', ['audited'])
        ->orderBy('date', 'desc')
        ->get();

        return Inertia::render('PettyCash/Audit/Index', [ 'pettyCash' => $pettyCash ]);
    }

    public function executiveApproval(Request $request, PettyCash $pettyCash)
    {
        $validated = $request->validate([
            'remarks' => 'nullable|string|max:500',
        ]);

        // Update petty cash status
        $pettyCash->update([
            'status' => 'approved',
        ]);

        PettyCashApproval::create([
            'petty_cash_id' => $pettyCash->id,
            'user_id'       => Auth::id(),
            'status'        => 'approved',
            'remarks'       => $request->input('remarks', ''),
            'approved_at'   => now(),
        ]);

        return back()->with('success', 'Approval saved successfully!');
    }

    public function executiveApprovalLiquidate(Request $request, PettyCash $pettyCash)
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

        return back()->with('success', 'Approval saved successfully!');
    }
}
