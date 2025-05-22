<?php

namespace App\Http\Controllers\Api;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

use App\Models\Voucher;
use App\Models\Account;
use App\Models\VoucherDetail;
use Illuminate\Http\Request;
use Spatie\Browsershot\Browsershot;

class ReportController extends Controller
{
    public function voucherReports()
    {
        return Inertia::render('Reports/Vouchers/Report', [
            'vouchers' => Voucher::with(['user', 'details'])->get(),
            'accounts' => Account::all(),
        ]);
    }

    public function generateVoucherReports(Voucher $voucher)
    {
        // Generate HTML content (you might want to create a dedicated view)
        $html = view('vouchers.report', compact('voucher'))->render();
        
        // Generate PDF
        $pdf = Browsershot::html($html)
            ->setNodeBinary(config('browsershot.node_path'))
            ->setNpmBinary(config('browsershot.npm_path'))
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