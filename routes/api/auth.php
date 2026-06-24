<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\TermsController;

Route::post('/logout', [AuthController::class, 'logout']);
Route::post('/logout-all', [AuthController::class, 'logoutAll']);
Route::get('/user', [AuthController::class, 'user']);
Route::put('/user/password', [AuthController::class, 'updatePassword']);
Route::get('/user/tokens', [AuthController::class, 'tokens']);
Route::delete('/user/tokens/{tokenId}', [AuthController::class, 'revokeToken']);
Route::post('/terms/accept', [TermsController::class, 'accept']);
