<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\VoucherController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\ApprovedRequestController;
use App\Http\Controllers\Api\RequestToOrderController;

use App\Http\Controllers\Configuration\UserAccessController;
use App\Http\Controllers\Configuration\DepartmentController;
use App\Http\Controllers\Configuration\SignatoryController;
use App\Http\Controllers\Configuration\AccountController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/api/vouchers', [VoucherController::class, 'store']);
Route::post('/upload-users', [UserController::class, 'uploadUsers']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);

// Voucher Routes
Route::apiResource('vouchers', VoucherController::class);
Route::put('/vouchers/{voucher}/details', [VoucherController::class, 'updateDetails']);
Route::put('/vouchers/{voucher}', [VoucherController::class, 'update']);
Route::get('/vouchers/{voucher}', [VoucherController::class, 'show']);

// Request Routes
Route::post('/requests', [RequestController::class, 'store']);
Route::put('/requests/{request}/items', [RequestController::class, 'updateItems']);
Route::post('/requests/{request}/release', [RequestController::class, 'releaseItems']);

Route::patch('/requests/{request}/tagging', [ApprovedRequestController::class, 'updateTagging']);

// All about Configurations
Route::prefix('configuration/departments')->group(function () {
    Route::post('/', [DepartmentController::class, 'store']);
    Route::put('/{department}', [DepartmentController::class, 'update']);
    Route::delete('/{department}', [DepartmentController::class, 'destroy']);
});

Route::prefix('configuration/access')->group(function () {
    Route::post('/', [UserAccessController::class, 'store']);
    Route::put('/{access}', [UserAccessController::class, 'update']);
    Route::delete('/{access}', [UserAccessController::class, 'destroy']);
});

Route::prefix('configuration/signatories')->group(function () {
    Route::post('/', [SignatoryController::class, 'store']);
    Route::put('/{signatory}', [SignatoryController::class, 'update']);
    Route::delete('/{signatory}', [SignatoryController::class, 'destroy']);
});

Route::prefix('configuration/accounts')->group(function () {
    Route::post('/', [AccountController::class, 'store']);
    Route::put('/{account}', [AccountController::class, 'update']);
    Route::delete('/{account}', [AccountController::class, 'destroy']);
});

// Purchase Order Routes
Route::apiResource('purchase-orders', PurchaseOrderController::class)->only(['store']);
Route::patch('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve']);
Route::patch('purchase-orders/{purchase_order}/reject', [PurchaseOrderController::class, 'reject']);
