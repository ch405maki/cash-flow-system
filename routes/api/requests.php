<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RequestController;
use App\Http\Controllers\Api\ApprovedRequestController;

Route::get('/requests', [RequestController::class, 'index']);
Route::post('/requests', [RequestController::class, 'store']);
Route::put('/requests/{request}/items', [RequestController::class, 'updateItems']);
Route::post('/requests/{request}/release', [RequestController::class, 'releaseItems']);
Route::put('/requests/{id}/purpose', [RequestController::class, 'updatePurpose']);
Route::patch('/requests/{request}/tagging', [ApprovedRequestController::class, 'updateTagging']);
Route::patch('/requests/{request}/status', [RequestController::class, 'updateStatus']);
Route::get('/requests/{request}/release-data', [RequestController::class, 'releaseData']);
Route::get('/requests/{request}/show-data', [RequestController::class, 'showData']);
Route::get('/requests/create-data', [RequestController::class, 'createData']);
Route::get('/requests/{request}/edit-data', [RequestController::class, 'editData']);
