<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\RequestToOrderController;
use App\Http\Controllers\Web\RequestToOrderApprovalController;
use App\Http\Controllers\Web\RequestToOrderReleaseController;
use App\Http\Controllers\Web\RequestToOrderApprovedController;

Route::prefix('request-to-order')->middleware(['auth', 'verified'])->group(function () {
    // Static routes (must come before parameterized routes)
    Route::get('/', [RequestToOrderController::class, 'index'])->name('request-to-order.index');
    Route::get('/create', [RequestToOrderController::class, 'create'])->name('request-to-order.create');
    Route::get('/list-to-order', [RequestToOrderController::class, 'list'])->name('request-to-order.list-to-order');
    Route::get('/on-process', [RequestToOrderApprovalController::class, 'onProcess'])->name('request-to-order.on-process');
    Route::get('/for-approval', [RequestToOrderApprovalController::class, 'index'])->name('request-to-order.for-approval');
    Route::get('/on-process-orders', [RequestToOrderReleaseController::class, 'index'])->name('request-to-order.on-process-orders');
    Route::get('/approved', [RequestToOrderApprovedController::class, 'index'])->name('request-to-order.approved');

    Route::post('/store', [RequestToOrderController::class, 'store'])->name('request-to-order.store');
    Route::post('/store-manual', [RequestToOrderController::class, 'storeManual'])->name('request-to-order.store-manual');

    // Parameterized routes (must come after static routes)
    Route::get('/{order}', [RequestToOrderController::class, 'show'])->name('request-to-order.show');
    Route::patch('/{order}/approve', [RequestToOrderController::class, 'approve'])->name('request-to-order.approve');
    Route::patch('/{order}/for-eod', [RequestToOrderController::class, 'forEod'])->name('request-to-order.for-eod');
    Route::patch('/{order}/reject', [RequestToOrderController::class, 'reject'])->name('request-to-order.reject');
    Route::get('/{order}/release', [RequestToOrderReleaseController::class, 'create'])->name('request-to-order.release.create');
    Route::post('/{order}/release', [RequestToOrderReleaseController::class, 'store'])->name('request-to-order.release.store');
});
