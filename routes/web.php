<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\RequestToOrderController;
use App\Http\Controllers\Api\ApprovedRequestController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\SignatoryController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\ReportController;


Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {   
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/request', [RequestController::class, 'index'])->name('request.index');
    Route::get('/request/show/{request}', [RequestController::class, 'show'])->name('request.show');
    Route::get('/request/create', [RequestController::class, 'create'])->name('request.create');
    Route::get('/requests/{request}/edit', [RequestController::class, 'edit'])->name('requests.edit');
    Route::patch('/requests/{request}/status', [RequestController::class, 'updateStatus'])->name('request.updateStatus');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/request-to-order', [RequestToOrderController::class, 'index'])->name('request-to-order.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/approved-request', [ApprovedRequestController::class, 'index'])->name('approved-request.index');
    Route::get('/approved-request/show/{request}', [ApprovedRequestController::class, 'show'])->name('approved-request.show');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/vouchers', [VoucherController::class, 'index'])->name('vouchers.index');
    Route::get('/vouchers/create', [VoucherController::class, 'create'])->name('vouchers.create');
    Route::get('/vouchers/{voucher}/edit', [VoucherController::class, 'edit'])->name('vouchers.edit');
    Route::put('/vouchers/{voucher}', [VoucherController::class, 'update'])->name('vouchers.update');
    Route::get('/vouchers/{voucher}/view', [VoucherController::class, 'view'])->name('vouchers.view');
    Route::get('/vouchers/{voucher}/preview', [ReportController::class, 'preview'])->name('vouchers.preview');
    Route::get('/vouchers/{voucher}/pdf', [ReportController::class, 'generateVoucherReports'])->name('vouchers.pdf');
    Route::get('/reports/vouchers/{voucher}/report', [ReportController::class, 'generateVoucherReports'])->name('vouchers.report');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/reports/vouchers', [ReportController::class, 'voucherReports'])->name('reports.voucherReports');
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])
        ->name('purchase-orders.index');

    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])
        ->name('purchase-orders.show');

    Route::get('/purchase-orders/create/{request}', [PurchaseOrderController::class, 'create'])
        ->name('purchase-orders.create');
});

require __DIR__.'/settings.php';
require __DIR__.'/configuration.php';
require __DIR__.'/auth.php';
