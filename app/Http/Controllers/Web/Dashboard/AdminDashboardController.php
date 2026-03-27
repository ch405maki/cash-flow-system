<?php

namespace App\Http\Controllers\Web\Dashboard;

use Inertia\Inertia;
use App\Models\User;
use App\Models\Department;
use App\Models\Access;

class AdminDashboardController extends BaseDashboardController
{
    protected $user;
    
    public function __construct($user)
    {
        $this->user = $user;
    }
    
    public function index()
    {
        $userStats = [
            'totalUsers' => User::count(),
            'activeUsers' => User::where('status', 'active')->count(),
            'inactiveUsers' => User::where('status', 'inactive')->count(),
            'departments' => Department::withCount('users')->get(),
            'accessLevels' => Access::withCount('users')->get(),
            'users' => User::with(['department', 'access'])->get(),
        ];

        return Inertia::render('Dashboard/Admin/Index', [
            'userRole' => $this->user->role,
            'username' => $this->user->username,
            'userStats' => $userStats,
        ]);
    }
}