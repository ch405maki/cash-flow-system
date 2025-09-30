<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Api\PettyCashController;
use App\Http\Controllers\Api\AuditPettyCashController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/petty-cash', [PettyCashController::class, 'index'])->name('petty-cash.index');
    Route::get('/petty-cash/create', [PettyCashController::class, 'create'])->name('petty-cash.create');
    Route::post('/petty-cash', [PettyCashController::class, 'store'])->name('petty-cash.store');
    Route::get('/petty-cash/{pettyCash}/edit', [PettyCashController::class, 'edit'])->name('petty-cash.edit');
    Route::put('/petty-cash/{pettyCash}', [PettyCashController::class, 'update'])->name('petty-cash.update');
    Route::delete('/petty-cash-items/{item}', [PettyCashItemController::class, 'destroy']);
    Route::put('/petty-cash/{id}/submit', [PettyCashController::class, 'submit'])->name('petty-cash.submit');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/audit/petty-cash', [AuditPettyCashController::class, 'index'])->name('audit.petty-cash.index');
    Route::get('/petty-cash/{pettyCash}/view', [AuditPettyCashController::class, 'view'])->name('petty-cash.view');
});




