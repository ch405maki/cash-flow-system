<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\ApprovedVoucherController;
use App\Http\Controllers\Api\VoucherApprovalController;
use App\Http\Controllers\Api\ApprovedPurchaseOrderController;
use App\Http\Controllers\Api\ReportController;

// Voucher Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
    Route::get('/vouchers/{voucher}/edit', [VoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('/vouchers/{voucher}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::get('/vouchers/{voucher}/view', [VoucherController::class, 'view'])->name('vouchers.view');
    Route::get('/vouchers/by-po/{po_id}', [VoucherController::class, 'viewWithPO'])->name('vouchers.viewWithPO');
    Route::patch('/vouchers/{voucher}/forDirector', [VoucherController::class, 'forDirector'])->name('vouchers.director');
    Route::get('/vouchers/{voucher}/download-receipt', [VoucherController::class, 'downloadReceipt'])->name('vouchers.download.receipt');

    Route::get('/vouchers/{voucher}/preview', [ReportController::class, 'preview'])->name('vouchers.preview');
    Route::get('/vouchers/{voucher}/pdf', [ReportController::class, 'generateVoucherReports'])->name('vouchers.pdf');
    Route::get('/reports/vouchers/{voucher}/report', [ReportController::class, 'generateVoucherReports'])->name('vouchers.report');
    
    Route::get('/voucher-approval', [VoucherApprovalController::class, 'index'])->name('voucher-approval.index');
    Route::patch('/vouchers/{voucher}/auditreview', [VoucherApprovalController::class, 'auditreview'])->name('vouchers.eod');
    Route::patch('/vouchers/{voucher}/auditreturn', [VoucherApprovalController::class, 'auditreturn'])->name('vouchers.return');

    Route::get('/for-voucher', [ApprovedPurchaseOrderController::class, 'forVoucher'])->name('for-voucher.index');
    Route::get('/approved-voucher', [ApprovedVoucherController::class, 'index'])->name('approved-voucher.index');

});