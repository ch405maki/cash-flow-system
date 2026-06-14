<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Web\ReceivingController;

Route::apiResource('purchase-order', PurchaseOrderController::class)->only(['store']);
Route::patch('purchase-order/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus']);
Route::post('purchase-order/{purchaseOrder}/receiving', [ReceivingController::class, 'store']);
