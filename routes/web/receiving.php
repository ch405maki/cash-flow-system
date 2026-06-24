<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Web\PurchaseOrderController;

Route::middleware('auth')->group(function () {
    Route::get('/receiving', [PurchaseOrderController::class, 'index'])->name('receiving.index');
});
