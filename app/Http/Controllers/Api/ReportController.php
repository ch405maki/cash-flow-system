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
use Spatie\Browsershot\Browsershot;

class ReportController extends Controller
{
    public function voucherReports()
    {
        return Inertia::render('Reports/Vouchers/Reports', [
            'vouchers' => Voucher::with(['user', 'details'])->get(),
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

        return inertia('Reports/Vouchers/ReportPreview', [
            'voucher' => $voucher,
            'pdfHtml' => view('vouchers.pdf-template', [
                'voucher' => $voucher,
                'signatories' => $signatories, // Pass signatories to the view
                'roles' => $roles

            ])->render(),
            'pdfUrl' => route('vouchers.pdf', $voucher),
            'authUser' => auth()->user()
        ]);
    }

    public function generateVoucherReports(Voucher $voucher)
    {
        // PDF generation remains the same
        $signatories = Signatory::where('status', 'active')->get();
        $roles = [
        'approved_by' => User::where('role', 'department_head')->first(),
        'exec_director' => User::where('role', 'executive_director')->first()
        ];

        $html = view('vouchers.pdf-template', [
            'voucher' => $voucher,
            'signatories' => $signatories,
            'roles' => $roles
        ])->render();
        
        $pdf = Browsershot::html($html)
            ->setNodeBinary('C:\Program Files\nodejs\node.exe')
            ->setNpmBinary('C:\Program Files\nodejs\npm.cmd')
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