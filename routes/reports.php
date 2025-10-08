<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Report\RequestReportController;
use App\Http\Controllers\Api\ReportController;


// Report Route 
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/po-summary', [ReportController::class, 'poSummary'])->name('reports.po-summary');
    Route::get('/reports/request-summary', [ReportController::class, 'requestSummary'])->name('reports.request-summary');
    Route::get('/reports/voucher-summary', [ReportController::class, 'voucherSummary'])->name('reports.voucher-summary');
    
    Route::get('/reports/request-released', [RequestReportController::class, 'releasedItems'])->name('reports.request.released.index');
    Route::get('/reports/petty-cash', [ReportController::class, 'pettyCashReport'])->name('reports.petty-cash');

    // custodian
    Route::get('/reports/request', [ReportController::class, 'requestReport'])->name('reports.request');
});