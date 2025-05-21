<?php

namespace App\Http\Controllers\Api;

use Inertia\Inertia;
use App\Http\Controllers\Controller;

class ReportController extends Controller
{
    public function index()
    {
        return Inertia::render('Reports/Index');
    }
}