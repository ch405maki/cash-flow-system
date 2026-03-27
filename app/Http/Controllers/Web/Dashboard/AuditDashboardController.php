<?php

namespace App\Http\Controllers\Web\Dashboard;

use Inertia\Inertia;
use App\Models\Canvas;
use App\Models\Voucher;
use App\Models\PettyCash;

class AuditDashboardController extends BaseDashboardController
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function index()
    {
        $pendingCanvasses = Canvas::where('status', 'submitted')
            ->whereDoesntHave('approvals', function ($query) {
                $query->where('role', 'audit');
            })
            ->count();

        $pendingVouchers = Voucher::where('status', 'forAudit')->count();
        $pendingPettyCash = PettyCash::where('status', 'submitted')->count();

        $recentCanvasses = Canvas::with(['creator', 'files', 'approvals'])
            ->where('status', 'submitted')
            ->whereDoesntHave('approvals', function ($query) {
                $query->where('role', 'audit');
            })
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($canvas) {
                return [
                    'id' => $canvas->id,
                    'title' => $canvas->title,
                    'status' => $canvas->status,
                    'files_count' => $canvas->files->count(),
                    'created_at' => $canvas->created_at,
                ];
            });

        $recentVouchers = Voucher::with(['user'])
            ->where('status', 'forAudit')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($voucher) {
                return [
                    'id' => $voucher->id,
                    'voucher_no' => $voucher->voucher_no,
                    'payee' => $voucher->payee,
                    'check_amount' => $voucher->check_amount,
                    'created_at' => $voucher->created_at,
                ];
            });

        $recentPettyCash = PettyCash::with(['user', 'items'])
            ->where('status', 'submitted')
            ->latest()
            ->limit(5)
            ->get()
            ->map(function ($pettyCash) {
                $totalAmount = $pettyCash->items->sum('amount');
                return [
                    'id' => $pettyCash->id,
                    'pcv_no' => $pettyCash->pcv_no,
                    'paid_to' => $pettyCash->paid_to,
                    'remarks' => $pettyCash->remarks,
                    'date' => $pettyCash->date,
                    'total_amount' => $totalAmount,
                    'items_count' => $pettyCash->items->count(),
                    'created_at' => $pettyCash->created_at,
                ];
            });

        return Inertia::render('Dashboard/Audit/Index', [
            'isDepartmentUser' => true,
            'userRole' => $this->user->role,
            'username' => $this->user->username,
            'dashboardStats' => [
                'pendingCanvasses' => $pendingCanvasses,
                'pendingVouchers' => $pendingVouchers,
                'pendingPettyCash' => $pendingPettyCash,
                'totalPending' => $pendingCanvasses + $pendingVouchers + $pendingPettyCash,
            ],
            'recentPendingItems' => [
                'canvasses' => $recentCanvasses,
                'vouchers' => $recentVouchers,
                'pettyCash' => $recentPettyCash,
            ],
        ]);
    }
}