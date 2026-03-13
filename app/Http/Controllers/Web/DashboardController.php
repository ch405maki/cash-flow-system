<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Web\Dashboard\AdminDashboardController;
use App\Http\Controllers\Web\Dashboard\AccountingDashboardController;
use App\Http\Controllers\Web\Dashboard\DepartmentDashboardController;
use App\Http\Controllers\Web\Dashboard\CustodianDashboardController;
use App\Http\Controllers\Web\Dashboard\BursarDashboardController;
use App\Http\Controllers\Web\Dashboard\AuditDashboardController;
use App\Http\Controllers\Web\Dashboard\PurchasingDashboardController;
use App\Http\Controllers\Web\Dashboard\ExecutiveDashboardController;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Apply middleware checks here if needed
        // You can add policy checks or gate checks
        
        // Redirect to appropriate dashboard based on user role
        if (in_array($user->role, ['staff', 'department_head'])) {
            $controller = new DepartmentDashboardController($user);
            return $controller->index();
        }
        elseif ($user->role === 'property_custodian') {
            $controller = new CustodianDashboardController($user);
            return $controller->index();
        }
        elseif ($user->role === 'executive_director') {
            $controller = new ExecutiveDashboardController($user);
            return $controller->index();
        }
        elseif ($user->role === 'purchasing') {
            $controller = new PurchasingDashboardController($user);
            return $controller->index();
        }
        elseif ($user->role === 'accounting') {
            $controller = new AccountingDashboardController($user);
            return $controller->index();
        }
        elseif ($user->role === 'admin') {
            $controller = new AdminDashboardController($user);
            return $controller->index();
        }
        elseif ($user->role === 'bursar') {
            $controller = new BursarDashboardController($user);
            return $controller->index();
        }
        elseif ($user->role === 'audit') {
            $controller = new AuditDashboardController($user);
            return $controller->index();
        }
        
        // Default dashboard for other roles
        return Inertia::render('Dashboard/Index', [
            'userRole' => $user->role,
        ]);
    }
}