<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Account;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function index()
    {
        $accounts = Account::all();
        return Inertia::render('Configuration/Accounts', [
            'accounts' => $accounts
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'account_title' => 'required|string|max:255|unique:accounts',
        ]);

        $account = Account::create($validated);

        return response()->json($account, 201);
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'account_title' => 'required|string|max:255|unique:accounts,account_title,'.$account->id,
        ]);

        $account->update($validated);

        return response()->json($account);
    }

    public function destroy(Account $account)
    {
        $account->delete();
        return response()->json(null, 204);
    }
}