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
    public function voucherReports(Request $request)
    {
        $query = Voucher::with(['user', 'details.account'])
            ->latest();

        // Add search functionality
        if ($request->has('search') && !empty($request->search)) {
            $searchTerm = $request->search;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('voucher_no', 'like', "%{$searchTerm}%");

            });
        }

        // Paginate the results (default 15 per page)
        $vouchers = $query->paginate($request->per_page ?? 10)
            ->appends($request->query());

        return Inertia::render('Reports/Vouchers/Reports', [
            'vouchers' => $vouchers,
            'accounts' => Account::all(),
            'filters' => $request->only(['search'])
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
            'isSalary' => $voucher->type === 'salary'
        ])->render();

        $pdf = Browsershot::html($html)
            ->waitUntilNetworkIdle()
            ->emulateMedia('screen')
            ->format('A4')
            ->pdf();

        return response()->streamDownload(
            fn() => print ($pdf),
            "voucher-report-{$voucher->voucher_no}.pdf",
            ['Content-Type' => 'application/pdf']
        );
    }
}