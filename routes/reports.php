<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Report\RequestReportController;

Route::get('/request-reports', [RequestReportController::class, 'releasedItems'])->name('request-reports.index');