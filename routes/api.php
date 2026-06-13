<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    require __DIR__.'/api/auth.php';
    require __DIR__.'/api/users.php';
    require __DIR__.'/api/requests.php';
    require __DIR__.'/api/vouchers.php';
    require __DIR__.'/api/purchase-orders.php';
    require __DIR__.'/api/configuration.php';
    require __DIR__.'/api/inventory.php';
});
