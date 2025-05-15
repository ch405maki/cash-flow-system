<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\SignatoryController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\PurchaseOrderController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/api/vouchers', [VoucherController::class, 'store']);
Route::post('/upload-users', [UserController::class, 'uploadUsers']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::put('/vouchers/{voucher}/details', [VoucherController::class, 'updateDetails']);
Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);
Route::get('/vouchers/next-number', [VoucherController::class, 'getNextVoucherNumber']);
Route::get('/vouchers/{voucher}/edit', [VoucherController::class, 'edit']);


Route::apiResource('requests', RequestController::class);
Route::apiResource('departments', DepartmentController::class);
Route::apiResource('access', AccessController::class);
Route::apiResource('vouchers', VoucherController::class);

Route::apiResource('accounts', AccountController::class);
Route::apiResource('signatories', SignatoryController::class);


Route::apiResource('purchase-orders', PurchaseOrderController::class)->only(['store']);

// Additional custom route for PO approval
Route::patch('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve']);
Route::patch('purchase-orders/{purchase_order}/reject', [PurchaseOrderController::class, 'reject']);