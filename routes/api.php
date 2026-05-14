<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
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
use App\Http\Controllers\Api\ProfilePictureController;
use App\Http\Controllers\Api\TermsController;
use App\Http\Controllers\Api\CanvasController;
use App\Http\Controllers\Api\InventoryController;

/*
|--------------------------------------------------------------------------
| Public API Routes (no authentication required)
|--------------------------------------------------------------------------
*/
Route::post('/login', [AuthController::class, 'login']);

/*
|--------------------------------------------------------------------------
| Authenticated API Routes (require Sanctum token)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Token management
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/logout-all', [AuthController::class, 'logoutAll']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/user/password', [AuthController::class, 'updatePassword']);
    Route::get('/user/tokens', [AuthController::class, 'tokens']);
    Route::delete('/user/tokens/{tokenId}', [AuthController::class, 'revokeToken']);

    // Existing authenticated endpoints
    Route::post('/terms/accept', [TermsController::class, 'accept']);

    // User management
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
    Route::post('/users', [UserController::class, 'store']);
    Route::post('/upload-users', [UserController::class, 'uploadUsers']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::put('/users/{id}/password', [UserController::class, 'updatePassword']);
    Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);

    // Profile pictures
    Route::prefix('profile-pictures')->group(function () {
        Route::post('/', [ProfilePictureController::class, 'store'])->name('profile-pictures.store');
        Route::delete('/{profilePicture}', [ProfilePictureController::class, 'destroy'])->name('profile-pictures.destroy');
    });

    // Voucher Routes
    Route::get('/vouchers/check-number', [VoucherController::class, 'checkVoucherNumber']);
    Route::get('/vouchers/generate-number', [VoucherController::class, 'generateNewVoucherNumber']);
    Route::post('/api/vouchers', [VoucherController::class, 'store']);
    Route::put('/vouchers/{voucher}/details', [VoucherController::class, 'updateDetails']);
    Route::put('/vouchers/{voucher}', [VoucherController::class, 'update']);
    Route::get('/vouchers/{voucher}', [VoucherController::class, 'show']);
    Route::post('/vouchers/{voucher}/receipt', [VoucherController::class, 'uploadReceipt']);
    Route::patch('/vouchers/{voucher}/check', [VoucherController::class, 'addCheckDetails']);
    Route::apiResource('vouchers', VoucherController::class);

    // Request Routes
    Route::get('/requests', [RequestController::class, 'data']);
    Route::post('/requests', [RequestController::class, 'store']);
    Route::put('/requests/{request}/items', [RequestController::class, 'updateItems']);
    Route::post('/requests/{request}/release', [RequestController::class, 'releaseItems']);
    Route::put('/requests/{id}/purpose', [RequestController::class, 'updatePurpose']);
    Route::patch('/requests/{request}/tagging', [ApprovedRequestController::class, 'updateTagging']);

    // Configurations
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

    // Canvas
    Route::get('/canvas/{canvas}/files/{file}/preview', [CanvasController::class, 'preview'])->name('canvas.preview.file');

    // Purchase Order Routes
    Route::apiResource('purchase-orders', PurchaseOrderController::class)->only(['store']);
    Route::patch('purchase-orders/{purchase_order}/approve', [PurchaseOrderController::class, 'approve']);
    Route::patch('purchase-orders/{purchase_order}/reject', [PurchaseOrderController::class, 'reject']);

    // Inventory
    Route::get('/inventory/products', [InventoryController::class, 'getProducts']);
    Route::get('/requests/{request}/check-inventory', [RequestController::class, 'checkInventoryAvailability']);
    Route::get('/inventory/items', [InventoryController::class, 'getItems']);
});