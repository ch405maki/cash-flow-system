<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\Voucher;
use App\Models\VoucherDetail;
use App\Models\Account;
use App\Models\Signatory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

use Illuminate\Database\QueryException;
use Inertia\Inertia;

class VoucherApprovalController extends Controller {
     public function index()
    {
        $user = Auth::user();

        return Inertia::render('Vouchers/ForApproval/Index', [
            'vouchers' => Voucher::with(['user', 'details'])
                                ->whereIn('status', ['forEOD'])
                                ->get(),
            'accounts' => Account::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'access' => $user->access_id,
            ]
        ]);
    }
}