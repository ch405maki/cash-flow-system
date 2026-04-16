<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Web\RequestController;
use App\Http\Controllers\Api\RequestApprovalController;
use App\Http\Controllers\Api\RequestToOrderController;
use App\Http\Controllers\Api\ApprovedRequestController;
use App\Http\Controllers\Api\RequestToOrderReleaseController;

// Request Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/request', [RequestController::class, 'index'])->name('request.index');
    Route::get('/request/show/{request}', [RequestController::class, 'show'])->name('request.show');
    Route::get('/request/create', [RequestController::class, 'create'])->name('request.create');
    Route::get('/requests/{request}/edit', [RequestController::class, 'edit'])->name('requests.edit');
    Route::get('/requests/{request}/release', [RequestController::class, 'release'])->name('requests.release');
    Route::patch('/requests/{request}/status', [RequestController::class, 'updateStatus'])->name('request.updateStatus');

    Route::get('/request/rejected', [RequestController::class, 'rejected'])->name('request.rejected');
    Route::get('/request/released', [RequestController::class, 'released'])->name('request.released');
    Route::get('/request/to-receive', [RequestController::class, 'toReceive'])->name('request.to-receive');
});

// Request To Order Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/request-to-order', [RequestToOrderController::class, 'index'])->name('request-to-order.index');
    Route::get('/request-to-order/create', [RequestToOrderController::class, 'create'])->name('request-to-order.create');
    Route::get('/request-to-order/list', [RequestToOrderController::class, 'list'])->name('request-to-order.list');
    Route::post('/request-to-orders', [RequestToOrderController::class, 'store'])->name('request-to-orders.store');
    Route::post('/request-to-orders/manual', [RequestToOrderController::class, 'storeManual'])->name('request-to-orders.storeManual');
    Route::get('/request-to-order/{id}', [RequestToOrderController::class, 'show'])->name('request-to-order.show');
    Route::patch('/request-to-order/{id}/approve', [RequestToOrderController::class, 'approve'])->name('request-to-order.approve');
    Route::patch('/request-to-order/{id}/for-eod', [RequestToOrderController::class, 'forEod'])->name('request-to-order.for-eod');
    Route::patch('/request-to-order/{id}/reject', [RequestToOrderController::class, 'reject'])->name('request-to-order.reject');
    
    Route::get('/to-order/on-process', [RequestApprovalController::class, 'onProcess'])->name('request-to-order.on-process');
    Route::get('/for-approval', [RequestApprovalController::class, 'index'])->name('for-approval.index');

    Route::get('/released-order', [RequestToOrderReleaseController::class, 'index'])->name('request-to-order.release.create');
    Route::get('/request-to-order/{order}/release', [RequestToOrderReleaseController::class, 'create'])->name('request-to-order.release.create');
    Route::post('/request-to-order/{order}/release', [RequestToOrderReleaseController::class, 'store'])->name('request-to-order.release.store');
});


Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/approved-request', [ApprovedRequestController::class, 'index'])->name('approved-request.index');
    Route::get('/approved-request/show/{request}', [ApprovedRequestController::class, 'show'])->name('approved-request.show');
});

