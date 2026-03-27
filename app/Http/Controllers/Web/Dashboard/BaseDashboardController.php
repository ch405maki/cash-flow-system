<?php

namespace App\Http\Controllers\Web\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Request;
use App\Models\RequestToOrder;
use App\Models\PurchaseOrder;
use App\Models\Voucher;
use App\Models\Canvas;
use App\Models\PettyCash;
use App\Models\CanvasApproval;
use App\Models\PurchaseOrderApproval;
use App\Models\RequestToOrderApproval;
use App\Models\VoucherApproval;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BaseDashboardController extends Controller
{
    protected $user;
    
    // Remove the constructor with middleware
    
    // Add a method to set user
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }
    
    // Shared helper methods
    protected function getMonthlyCounts($model)
    {
        return $model::selectRaw('MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('count', 'month')
            ->toArray();
    }

    protected function getMonthlyAmounts()
    {
        return PurchaseOrder::selectRaw('MONTH(created_at) as month, SUM(amount) as total')
            ->whereYear('created_at', now()->year)
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->pluck('total', 'month')
            ->toArray();
    }

    protected function getApprovalTimeTrends()
    {
        $months = 6;
        $currentDate = Carbon::now();
        $data = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $month = $currentDate->copy()->subMonths($i);
            $monthLabel = $month->format('M Y');

            $data['labels'][] = $monthLabel;
            $data['datasets']['canvas'][] = CanvasApproval::whereYear('approved_at', $month->year)
                ->whereMonth('approved_at', $month->month)
                ->where('approved', true)->count();
            $data['datasets']['purchase_orders'][] = PurchaseOrderApproval::whereYear('approved_at', $month->year)
                ->whereMonth('approved_at', $month->month)
                ->where('status', 'approved')->count();
            $data['datasets']['request_to_orders'][] = RequestToOrderApproval::whereYear('approved_at', $month->year)
                ->whereMonth('approved_at', $month->month)
                ->where('status', 'approved')->count();
            $data['datasets']['vouchers'][] = VoucherApproval::whereYear('approved_at', $month->year)
                ->whereMonth('approved_at', $month->month)
                ->where('status', 'approved')->count();
        }

        return $data;
    }
}