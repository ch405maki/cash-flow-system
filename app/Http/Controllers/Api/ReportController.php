<?php

namespace App\Http\Controllers\Api;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

use App\Models\User;
use App\Models\Voucher;
use App\Models\Account;
use App\Models\Signatory;
use App\Models\VoucherDetail;
use App\Models\Department;
use App\Models\PurchaseOrder;
use App\Models\PettyCash;
use App\Models\PettyCashItem;;
use Spatie\Browsershot\Browsershot;
use App\Models\Request;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Support\Facades\Auth;

class ReportController extends Controller
{
    public function index(){
        return Inertia::render('Reports/Index');
    }

    public function requestSummary()
    {
        $requests = Request::with(['department', 'user', 'details'])
            ->orderBy('request_date', 'desc')
            ->get();

        $departments = Department::orderBy('department_name')->get(['id', 'department_name as name']);

        return Inertia::render('Reports/Requests/Index', [
            'requests' => $requests,
            'departments' => $departments,
            'statuses' => ['pending', 'approved', 'rejected', 'completed']
        ]);
    }

    public function requestReport(HttpRequest $request)
    {
        $user = Auth::user();

        // Get the `status` query string (can be a string or array)
        $status = $request->query('status');

        $requests = Request::with(['department', 'user', 'details'])
            ->when($status, function ($query, $status) {
                if (is_array($status)) {
                    $query->whereIn('status', $status);
                } else {
                    $query->where('status', $status);
                }
            })
            ->get();

        return Inertia::render('Request/Index', [
            'requests' => $requests,
            'departments' => Department::all(),
            'authUser' => [
                'id' => $user->id,
                'role' => $user->role,
                'department_id' => $user->department_id,
            ],
        ]);
    }

    public function poSummary()
    {
        $purchaseOrders = PurchaseOrder::with('department')
            ->orderBy('date', 'desc')
            ->get(['id', 'po_no', 'date', 'payee', 'amount', 'department_id']);

        return Inertia::render('Reports/PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders
        ]);
    }

    public function voucherSummary()
    {
        $vouchers = Voucher::with(['details.account'])
            ->orderBy('voucher_date', 'desc')
            ->get(['id', 'voucher_no', 'voucher_date', 'payee', 'check_amount', 'type', 'purpose']);

        return Inertia::render('Reports/Vouchers/Index', [
            'vouchers' => $vouchers
        ]);
    }

    public function voucherReports()
    {
        return Inertia::render('Reports/Vouchers/Reports', [
            'vouchers' => Voucher::with(['user', 'details.account'])->get(),
            'accounts' => Account::all(),
        ]);
    }

    public function pettyCashReport(PettyCash $pettyCash)
    {
        $pettyCash = PettyCash::with(['items', 'user.department'])
            ->whereIn('status', ['released', 'approved liquidation'])
            ->orderByDesc('date')
            ->get();

        return Inertia::render('Reports/PettyCash/Index', [
            'pettyCash' => $pettyCash,
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