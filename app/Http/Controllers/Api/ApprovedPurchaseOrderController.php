<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Models\PurchaseOrder;
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

class ApprovedPurchaseOrderController extends Controller {
    public function forVoucher() {

        $user = Auth::user();
        // Get only purchase orders with status 'for_approval'
        $purchaseOrders = PurchaseOrder::with(['user', 'department', 'account', 'details'])
            ->where('status', 'approved')
            ->latest()
            ->paginate(10);
        
    
        return Inertia::render('Vouchers/ForVoucher/Index', ['purchaseOrders' => $purchaseOrders]);
    }

    public function createForVoucher()
    {
        return Inertia::render('Vouchers/ForVoucher/CreateForVoucher', [
            'accounts' => Account::orderBy('account_title')->get()
        ]);
    }
}