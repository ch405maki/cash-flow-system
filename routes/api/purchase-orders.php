<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PurchaseOrderController;

Route::apiResource('purchase-orders', PurchaseOrderController::class)->only(['store']);
Route::patch('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve']);
Route::patch('purchase-orders/{purchase_order}/reject', [PurchaseOrderController::class, 'reject']);
