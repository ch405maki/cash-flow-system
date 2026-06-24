<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\RequestController;

Route::get('/inventory/products', [InventoryController::class, 'getProducts']);
Route::get('/requests/{request}/check-inventory', [RequestController::class, 'checkInventoryAvailability']);
Route::get('/inventory/items', [InventoryController::class, 'getItems']);
