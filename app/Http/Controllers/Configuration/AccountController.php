<?php

namespace App\Http\Controllers\Configuration;

use App\Http\Controllers\Controller;
use App\Http\Requests\Configuration\StoreAccountRequest;
use App\Http\Requests\Configuration\UpdateAccountRequest;
use App\Models\Account;
use App\Services\ConfigurationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AccountController extends Controller
{
    public function __construct(
        protected ConfigurationService $configService
    ) {}

    public function index()
    {
        return Inertia::render('Configuration/Accounts', [
            'accounts' => Account::all()
        ]);
    }

    public function store(StoreAccountRequest $request)
    {
        $account = $this->configService->create(
            Account::class,
            $request->validated(),
            $request,
            'account_title'
        );

        return response()->json($account, 201);
    }

    public function update(UpdateAccountRequest $request, Account $account)
    {
        $account = $this->configService->update(
            $account,
            $request->validated(),
            $request,
            'account_title'
        );

        return response()->json($account);
    }

    public function destroy(Request $request, Account $account)
    {
        $this->configService->delete($account, $request, 'account_title');

        return response()->json(null, 204);
    }
}