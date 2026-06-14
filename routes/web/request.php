<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\RequestController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/request', [RequestController::class, 'index'])->name('request.index');
    Route::get('/request/show/{request}', [RequestController::class, 'show'])->name('request.show');
    Route::get('/request/create', [RequestController::class, 'create'])->name('request.create');
    Route::get('/requests/{request}/edit', [RequestController::class, 'edit'])->name('requests.edit');
    Route::get('/requests/{request}/release', [RequestController::class, 'release'])->name('requests.release');
});

