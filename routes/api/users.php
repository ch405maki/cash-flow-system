<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\ProfilePictureController;

Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/upload-users', [UserController::class, 'uploadUsers']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::put('/users/{id}/password', [UserController::class, 'updatePassword']);
Route::patch('/users/{user}/status', [UserController::class, 'updateStatus']);

Route::prefix('profile-pictures')->group(function () {
    Route::post('/', [ProfilePictureController::class, 'store']);
    Route::delete('/{profilePicture}', [ProfilePictureController::class, 'destroy']);
});
