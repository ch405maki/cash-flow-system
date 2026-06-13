<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Configuration\DepartmentController;
use App\Http\Controllers\Configuration\UserAccessController;
use App\Http\Controllers\Configuration\SignatoryController;
use App\Http\Controllers\Configuration\AccountController;

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
