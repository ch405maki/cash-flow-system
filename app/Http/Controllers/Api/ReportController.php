<?php

namespace App\Http\Controllers\Api;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Voucher;
use App\Models\Account;
use App\Models\Signatory;
use App\Models\VoucherDetail;
use Illuminate\Http\Request;
use App\Models\PurchaseOrder;
use Spatie\Browsershot\Browsershot;

class ReportController extends Controller
{
    public function index(){
        return Inertia::render('Reports/Index');
    }

    public function poSummary()
    {
        $purchaseOrders = PurchaseOrder::with('department')
            ->where('status', 'approved')
            ->orderBy('date', 'desc')
            ->get(['id', 'po_no', 'date', 'payee', 'amount', 'department_id']);

        return Inertia::render('Reports/PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders
        ]);
    }

    public function voucherReports()
    {
        return Inertia::render('Reports/Vouchers/Reports', [
            'vouchers' => Voucher::with(['user', 'details.account'])->get(),
            'accounts' => Account::all(),
        ]);
    }

   public function preview(Voucher $voucher)
    {
        $signatories = Signatory::where('status', 'active')->get();
        $roles = [
            'approved_by' => User::where('role', 'department_head')->first(),
            'exec_director' => User::where('role', 'executive_director')->first()
        ];
        
        // Eager load necessary relationships
        $voucher->load(['details.account', 'user']);

        return inertia('Reports/Vouchers/ReportPreview', [
            'voucher' => $voucher,
            'pdfHtml' => view('vouchers.voucher-report', [
                'voucher' => $voucher,
                'signatories' => $signatories,
                'roles' => $roles,
                'isSalary' => $voucher->type === 'salary'
            ])->render(),
            'pdfUrl' => route('vouchers.pdf', $voucher),
            'authUser' => auth()->user()
        ]);
    }


    public function generateVoucherReports(Voucher $voucher)
    {
        $signatories = Signatory::where('status', 'active')->get();
        $roles = [
            'approved_by' => User::where('role', 'department_head')->first(),
            'exec_director' => User::where('role', 'executive_director')->first()
        ];
        
        // Eager load for PDF generation too
        $voucher->load(['details.account', 'user']);

        $html = view('vouchers.voucher-report', [
            'voucher' => $voucher,
            'signatories' => $signatories,
            'roles' => $roles,
            'isSalary' => $voucher->type === 'salary' // Add this flag
        ])->render();
        
        $pdf = Browsershot::html($html)
            // ->setNodeBinary('C:\Program Files\nodejs\node.exe')
            // ->setNpmBinary('C:\Program Files\nodejs\npm.cmd')
            ->waitUntilNetworkIdle()
            ->emulateMedia('screen')
            ->format('A4')
            ->pdf();
            
        return response()->streamDownload(
            fn () => print($pdf),
            "voucher-report-{$voucher->voucher_no}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}