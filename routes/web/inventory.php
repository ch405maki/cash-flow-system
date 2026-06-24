<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Web\InventoryController;

Route::middleware('auth')->group(function () {
    Route::get('inventory', [InventoryController::class, 'index'])->name('inventory');
});
