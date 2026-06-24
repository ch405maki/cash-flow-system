<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\VoucherController;

Route::get('/vouchers/check-number', [VoucherController::class, 'checkVoucherNumber']);
Route::get('/vouchers/generate-number', [VoucherController::class, 'generateNewVoucherNumber']);
Route::post('/api/vouchers', [VoucherController::class, 'store']);
Route::put('/vouchers/{voucher}/details', [VoucherController::class, 'updateDetails']);
Route::put('/vouchers/{voucher}', [VoucherController::class, 'update']);
Route::get('/vouchers/{voucher}', [VoucherController::class, 'show']);
Route::post('/vouchers/{voucher}/receipt', [VoucherController::class, 'uploadReceipt']);
Route::patch('/vouchers/{voucher}/check', [VoucherController::class, 'addCheckDetails']);
Route::apiResource('vouchers', VoucherController::class);
