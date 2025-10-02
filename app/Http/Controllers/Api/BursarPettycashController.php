<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PettyCash;
use App\Models\PettyCashItem;
use Illuminate\Support\Facades\Auth;
use App\Models\PettyCashApproval;

class BursarPettycashController extends Controller
{
    public function index()
    {
        $pettyCash = PettyCash::with('items')
        ->where('status', 'approved')
        ->orderBy('date', 'desc')
        ->get();

        return Inertia::render('PettyCash/Bursar/Index', [ 'pettyCash' => $pettyCash ]);
    }

    public function view(PettyCash $pettyCash)
    {
        $pettyCash->load('items');

        return Inertia::render('PettyCash/Bursar/View', [
            'pettyCash' => $pettyCash,
        ]);
    }

    public function release(Request $request, PettyCash $pettyCash)
    {
        if ($pettyCash->status !== 'approved') {
            return back()->withErrors([
                'status' => 'Only approved petty cash can be released.'
            ]);
        }

        $request->validate([
            'reimbursement_total' => 'nullable|numeric',
            'liquidation_total'   => 'nullable|numeric',
            'grand_total'         => 'nullable|numeric',
        ]);

        $pettyCash->status = 'released';
        $pettyCash->save();

        $grandTotal = $request->input('grand_total', 0);
        $remarks = $request->input(
            'remarks',
            'Released by bursar ₱' . number_format($grandTotal, 2)
        );

        PettyCashApproval::create([
            'petty_cash_id' => $pettyCash->id,
            'user_id'       => Auth::id(),
            'status'        => 'released',
            'remarks'       => $remarks,
            'approved_at'   => now(),
        ]);

        return redirect()
            ->route('bursar.petty-cash.index')
            ->with('success', 'Petty Cash released successfully.');
    }
}
