<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\RequestApprovalController;
use App\Http\Controllers\Api\VoucherApprovalController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\RequestToOrderController;
use App\Http\Controllers\Api\ApprovedRequestController;
use App\Http\Controllers\Api\ApprovedVoucherController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\SignatoryController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\CanvasController;
use App\Http\Controllers\Api\DashboardController;


Route::get('/', function () {
    return Inertia::render('auth/Login');
})->name('home');

// Dashboard Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Request Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/request', [RequestController::class, 'index'])->name('request.index');
    Route::get('/request/show/{request}', [RequestController::class, 'show'])->name('request.show');
    Route::get('/request/create', [RequestController::class, 'create'])->name('request.create');
    Route::get('/requests/{request}/edit', [RequestController::class, 'edit'])->name('requests.edit');
    Route::get('/requests/{request}/release', [RequestController::class, 'release'])->name('requests.release');
    Route::patch('/requests/{request}/status', [RequestController::class, 'updateStatus'])->name('request.updateStatus');

    Route::get('/request/rejected', [RequestController::class, 'rejected'])->name('request.rejected');
    Route::get('/request/to-receive', [RequestController::class, 'toReceive'])->name('request.to-receive');
});

// Request To Order Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/request-to-order', [RequestToOrderController::class, 'index'])->name('request-to-order.index');
    Route::get('/request-to-order/create', [RequestToOrderController::class, 'create'])->name('request-to-order.create');
    Route::get('/request-to-order/list', [RequestToOrderController::class, 'list'])->name('request-to-order.list');
    Route::post('/request-to-orders', [RequestToOrderController::class, 'store'])->name('request-to-orders.store');
    Route::get('/request-to-order/{id}', [RequestToOrderController::class, 'show'])->name('request-to-order.show');
    
    Route::patch('/request-to-order/{id}/approve', [RequestToOrderController::class, 'approve'])->name('request-to-order.approve');
    Route::patch('/request-to-order/{id}/for-eod', [RequestToOrderController::class, 'forEod'])->name('request-to-order.for-eod');
    Route::patch('/request-to-order/{id}/reject', [RequestToOrderController::class, 'reject'])->name('request-to-order.reject');
    
    Route::get('/for-approval', [RequestApprovalController::class, 'index'])->name('for-approval.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/approved-request', [ApprovedRequestController::class, 'index'])->name('approved-request.index');
    Route::get('/approved-request/show/{request}', [ApprovedRequestController::class, 'show'])->name('approved-request.show');
});

// Voucher Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
    Route::get('/vouchers/{voucher}/edit', [VoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('/vouchers/{voucher}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::get('/vouchers/{voucher}/view', [VoucherController::class, 'view'])->name('vouchers.view');
    Route::get('/vouchers/{voucher}/preview', [ReportController::class, 'preview'])->name('vouchers.preview');
    Route::get('/vouchers/{voucher}/pdf', [ReportController::class, 'generateVoucherReports'])->name('vouchers.pdf');
    Route::get('/reports/vouchers/{voucher}/report', [ReportController::class, 'generateVoucherReports'])->name('vouchers.report');
    
    
    
    Route::patch('/vouchers/{voucher}/forDirector', [VoucherController::class, 'forDirector'])
     ->name('vouchers.director');
    Route::patch('/vouchers/{voucher}/forEod', [VoucherController::class, 'forEod'])
     ->name('vouchers.eod');

    Route::get('/for-voucher', [VoucherController::class, 'forVoucher'])->name('for-voucher.index');
    Route::get('/voucher-approval', [VoucherApprovalController::class, 'index'])->name('voucher-approval.index');
    Route::get('/approved-voucher', [ApprovedVoucherController::class, 'index'])->name('approved-voucher.index');
});

// Report Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/po-summary', [ReportController::class, 'poSummary'])->name('reports.po-summary');
    Route::get('/reports/request-summary', [ReportController::class, 'requestSummary'])->name('reports.request-summary');
    Route::get('/reports/voucher-summary', [ReportController::class, 'voucherSummary'])->name('reports.voucher-summary');
});

// Purchase Order Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])
        ->name('purchase-orders.index');

    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])
        ->name('purchase-orders.show');

    Route::get('/purchase-order/create', [PurchaseOrderController::class, 'create'])->name('purchase-order.create');
    Route::patch('/purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.status.update');
});

// Canvas Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/canvas/create', [CanvasController::class, 'create'])->name('canvas.create');
    Route::post('/canvas', [CanvasController::class, 'store'])->name('canvas.store');
    Route::get('/canvas', [CanvasController::class, 'index'])->name('canvas.index');

    Route::get('/canvas/approval', [CanvasController::class, 'approval'])->name('canvas.approval');
    Route::get('/canvas/{canvas}', [CanvasController::class, 'show'])->name('canvas.show');
    Route::get('/canvas/{canvas}/download', [CanvasController::class, 'download'])->name('canvas.download');
    Route::patch('/canvas/{canvas}', [CanvasController::class, 'update'])->name('canvas.update');
});

require __DIR__.'/settings.php';
require __DIR__.'/configuration.php';
require __DIR__.'/auth.php';
