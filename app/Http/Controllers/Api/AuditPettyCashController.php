<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\PettyCash;
use App\Models\PettyCashItem;
use Illuminate\Support\Facades\Auth;

class AuditPettyCashController extends Controller
{
    public function index()
    {
        $pettyCash = PettyCash::with('items')
        ->whereNot('status', 'audited')
        ->orderBy('date', 'desc')
        ->get();

        return Inertia::render('PettyCash/Audit/Index', [ 'pettyCash' => $pettyCash ]);
    }

    public function view(PettyCash $pettyCash)
    {
        $pettyCash->load('items');

        return Inertia::render('PettyCash/Audit/View', [
            'pettyCash' => $pettyCash,
        ]);
    }
}
