<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Web\ReceivingController;

Route::apiResource('purchase-order', PurchaseOrderController::class)->only(['store', 'destroy']);
Route::get('purchase-order/index-data', [PurchaseOrderController::class, 'indexData']);
Route::get('purchase-order/{purchase_order}/show-data', [PurchaseOrderController::class, 'showData']);
Route::get('purchase-order/create-data', [PurchaseOrderController::class, 'createData']);
Route::get('purchase-order/{purchase_order}/edit-data', [PurchaseOrderController::class, 'editData']);
Route::patch('purchase-order/{purchase_order}', [PurchaseOrderController::class, 'update']);
Route::patch('purchase-order/{purchase_order}/status', [PurchaseOrderController::class, 'updateStatus']);
Route::post('purchase-order/{purchaseOrder}/receiving', [ReceivingController::class, 'store']);
