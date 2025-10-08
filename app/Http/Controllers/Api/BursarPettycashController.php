<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PettyCash;
use App\Models\PettyCashItem;
use Illuminate\Support\Facades\Auth;
use App\Models\PettyCashApproval;
use App\Models\PettyCashFund;

class BursarPettycashController extends Controller
{
    public function index()
    {
        $pettyCashFund = PettyCashFund::where('user_id', Auth::id())->first();
        $pettyCash = PettyCash::with('items', 'user')
            ->where('status', 'approved')
            ->orderBy('date', 'desc')
            ->get();

        return Inertia::render('PettyCash/Index', [ 'pettyCash' => $pettyCash, 'pettyCashFund' => $pettyCashFund ]);
    }

    public function release(Request $request, PettyCash $pettyCash)
    {
        if ($pettyCash->status !== 'approved') {
            return back()->withErrors(['status' => 'Only approved petty cash can be released.']);
        }

        $grandTotal = $request->input('grand_total', 0);

        // Deduct from petty cash fund
        $fund = PettyCashFund::where('user_id', Auth::id())->first();

        if ($fund) {
            if ($fund->fund_amount < $grandTotal) {
                return back()->withErrors(['fund' => 'Insufficient petty cash fund balance.']);
            }

            $fund->fund_amount -= $grandTotal;
            $fund->save();
        }

        $pettyCash->status = 'released';
        $pettyCash->save();

        PettyCashApproval::create([
            'petty_cash_id' => $pettyCash->id,
            'user_id'       => Auth::id(),
            'status'        => 'released',
            'remarks'       => 'Released by bursar ₱' . number_format($grandTotal, 2),
            'approved_at'   => now(),
        ]);

        return redirect()->route('bursar.petty-cash.index')
            ->with('success', 'Petty Cash released successfully and deducted from fund.');
    }

    public function releaseCashAdvance(Request $request, PettyCash $pettyCash)
    {
        $grandTotal = $request->input('grand_total', 0);
        $userRemarks = $request->input('remarks', '');

        $pettyCash->status = 'for liquidation';
        $pettyCash->save();

        $autoMessage = 'Cash advance amounting to: ' . number_format($grandTotal, 2) . ' released.';
        $remarks = $userRemarks 
            ? $userRemarks . ' — ' . $autoMessage 
            : $autoMessage;

        PettyCashApproval::create([
            'petty_cash_id' => $pettyCash->id,
            'user_id'       => Auth::id(),
            'status'        => 'released',
            'remarks'       => $remarks,
            'approved_at'   => now(),
        ]);

        return redirect()->route('petty-cash.index')
            ->with('success', 'Petty Cash released successfully and deducted from fund.');
    }


}
