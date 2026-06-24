<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RequestToOrderController;

Route::get('/request-to-order', [RequestToOrderController::class, 'index']);
Route::get('/request-to-order/create-data', [RequestToOrderController::class, 'createData']);
Route::get('/request-to-order/list-data', [RequestToOrderController::class, 'listData']);
Route::get('/request-to-order/{order}', [RequestToOrderController::class, 'showData']);
Route::post('/request-to-order/store', [RequestToOrderController::class, 'store']);
Route::post('/request-to-order/store-manual', [RequestToOrderController::class, 'storeManual']);
Route::patch('/request-to-order/{order}/approve', [RequestToOrderController::class, 'approve']);
Route::patch('/request-to-order/{order}/for-eod', [RequestToOrderController::class, 'forEod']);
Route::patch('/request-to-order/{order}/reject', [RequestToOrderController::class, 'reject']);
Route::get('/request-to-order/{order}/release-data', [RequestToOrderController::class, 'releaseData']);
Route::post('/request-to-order/{order}/release', [RequestToOrderController::class, 'release']);
