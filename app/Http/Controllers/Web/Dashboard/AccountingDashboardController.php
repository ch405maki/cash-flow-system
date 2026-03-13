<?php

namespace App\Http\Controllers\Web\Dashboard;

use Inertia\Inertia;
use App\Models\PurchaseOrder;
use App\Models\Voucher;

class AccountingDashboardController extends BaseDashboardController
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with(['user', 'department', 'details'])
            ->orderBy('date', 'desc')
            ->where('status', 'approved')
            ->limit(10)
            ->get()
            ->map(function ($po) {
                return [
                    'id' => $po->id,
                    'po_no' => $po->po_no,
                    'date' => $po->date,
                    'payee' => $po->payee,
                    'amount' => $po->amount,
                    'purpose' => $po->purpose,
                    'status' => $po->status,
                    'department' => $po->department ? $po->department->name : null,
                    'user' => $po->user ? $po->user->only(['first_name', 'last_name']) : null,
                    'details' => $po->details->map(function ($detail) {
                        return [
                            'id' => $detail->id,
                            'quantity' => $detail->quantity,
                            'unit' => $detail->unit,
                            'item_description' => $detail->item_description,
                            'unit_price' => $detail->unit_price,
                            'amount' => $detail->amount
                        ];
                    }),
                ];
            });

        $voucherStats = [
            'totalVouchers' => Voucher::count(),
            'totalAmount' => Voucher::sum('check_amount'),
            'overdueVouchers' => Voucher::where('status', 'pending')
                ->whereDate('issue_date', '<=', now()->subDays(7))
                ->count(),
            'currentMonthVouchers' => Voucher::whereMonth('issue_date', now()->month)
                ->whereYear('issue_date', now()->year)
                ->count(),
            'statusCounts' => [
                'pending' => Voucher::where('status', 'pending')->count(),
                'forApproval' => Voucher::where('status', 'forEOD')->count(),
                'approved' => Voucher::where('status', 'forCheck')->count(),
                'paid' => Voucher::where('status', 'paid')->count(),
                'rejected' => Voucher::where('status', 'rejected')->count(),
            ],
            'monthlyData' => Voucher::selectRaw('YEAR(issue_date) as year, MONTH(issue_date) as month, COUNT(*) as count, SUM(check_amount) as amount')
                ->groupBy('year', 'month')
                ->orderBy('year', 'desc')
                ->orderBy('month', 'desc')
                ->get()
        ];

        $statusCounts = [
            'totalForVoucher' => PurchaseOrder::where('status', 'approved')->count(),
            'pending' => Voucher::where('status', 'draft')->count(),
            'forApproval' => Voucher::where('status', 'forEOD')->count(),
            'approved' => Voucher::whereIn('status', ['forCheck', 'unreleased', 'released'])->count(),
            'rejected' => Voucher::where('status', 'rejected')->count(),
        ];

        return Inertia::render('Dashboard/Accounting/Index', [
            'isDepartmentUser' => true,
            'purchaseOrders' => $purchaseOrders,
            'statusCounts' => $statusCounts,
            'voucherStats' => $voucherStats,
            'userRole' => $this->user->role,
            'username' => $this->user->username,
        ]);
    }
}