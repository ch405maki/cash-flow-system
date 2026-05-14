<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Services\ActivityLogger;
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

        ActivityLogger::make($request)
            ->log("Account \"{$account->account_title}\" created");

        return response()->json($account, 201);
    }

    public function update(Request $request, Account $account)
    {
        $validated = $request->validate([
            'account_title' => 'required|string|max:255|unique:accounts,account_title,'.$account->id,
        ]);

        $account->update($validated);

        ActivityLogger::make($request)
            ->log("Account \"{$account->account_title}\" updated");

        return response()->json($account);
    }

    public function destroy(Request $request, Account $account)
    {
        $title = $account->account_title;
        $account->delete();

        ActivityLogger::make($request)
            ->log("Account \"{$title}\" deleted");

        return response()->json(null, 204);
    }
}