<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Web\ReceivingController;
use App\Http\Controllers\Api\PurchaseOrderController;

Route::middleware('auth')->group(function () {
    Route::get('/receiving', [PurchaseOrderController::class, 'index'])->name('receiving.index');
    Route::get('/purchase-orders/{purchaseOrder}/receiving', [ReceivingController::class, 'show'])->name('receiving.show');
    Route::post('/purchase-orders/{purchaseOrder}/receiving', [ReceivingController::class, 'store'])->name('receiving.store');
});
