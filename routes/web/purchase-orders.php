<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\PurchaseOrderController;
use App\Http\Controllers\Web\ReceivingController;

Route::prefix('purchase-order')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [PurchaseOrderController::class, 'index'])->name('purchase-order.index');
    Route::get('/create', [PurchaseOrderController::class, 'create'])->name('purchase-order.create');
    Route::get('/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-order.show');
    Route::get('/{purchaseOrder}/receiving', [ReceivingController::class, 'show'])->name('receiving.show');
});
