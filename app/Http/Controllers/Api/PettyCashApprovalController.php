<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\PettyCash;
use App\Models\PettyCashApproval;
use Illuminate\Support\Facades\Auth;

class PettyCashApprovalController extends Controller
{
    public function store(Request $request, PettyCash $pettyCash)
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
