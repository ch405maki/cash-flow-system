<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PettyCash;
use App\Models\PettyCashItem;
use Illuminate\Support\Facades\Auth;
use App\Models\Account;
use App\Models\DistributionExpense;

class AuditPettyCashController extends Controller
{
    public function index()
    {
        $pettyCash = PettyCash::with('items')
            ->whereNotIn('status', ['draft', 'for liquidation'])
            ->orderBy('date', 'desc')
            ->get();

        return Inertia::render('PettyCash/Audit/Index', [ 'pettyCash' => $pettyCash ]);
    }

    public function view(PettyCash $pettyCash)
    {
        $user = auth()->user();

        $pettyCash->load([
            'items',
            'approvals.user',
            'distributionExpenses',
        ]);

        $accounts = Account::orderBy('account_title')->get();

        if ($user->role === 'audit') {
            // Accounting sees all requested petty cash
            return Inertia::render('PettyCash/Audit/View', [
                'pettyCash' => $pettyCash,
                'accounts' => $accounts,
            ]);
        } else {
            // Other users only see their own department’s
            return Inertia::render('PettyCash/View', [
                'pettyCash' => $pettyCash,
                'accounts' => $accounts,
            ]);
        }
    }

    public function storeDistribution(Request $request, PettyCash $pettyCash)
    {
        $validated = $request->validate([
            'account_id' => 'required|exists:accounts,id',
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'prepared_by' => 'nullable|integer|exists:users,id',
            'approved_by' => 'nullable|integer|exists:users,id',
            'audited_by' => 'nullable|integer|exists:users,id',
            'paid_by' => 'nullable|integer|exists:users,id',
        ]);

        $account = Account::find($validated['account_id']);

        $validated['account_name'] = $account->account_title;
        $validated['petty_cash_id'] = $pettyCash->id;
        $validated['audited_by'] = Auth::id();
        $validated['prepared_by'] = Auth::id();

        DistributionExpense::create($validated);

        return redirect()
            ->back()
            ->with('success', 'Distribution of expense recorded successfully.');
    }

}
