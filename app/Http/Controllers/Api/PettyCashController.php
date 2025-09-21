<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PettyCashController extends Controller
{
    public function index()
    {
        return Inertia::render('PettyCash/Index');
    }
}
