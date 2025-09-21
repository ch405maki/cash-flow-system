<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Api\PettyCashController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/petty-cash', [PettyCashController::class, 'index'])->name('petty-cash.index');
});

