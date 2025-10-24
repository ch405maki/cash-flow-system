<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Api\PettyCashController;
use App\Http\Controllers\Api\AuditPettyCashController;
use App\Http\Controllers\Api\PettyCashApprovalController;
use App\Http\Controllers\Api\BursarPettycashController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/petty-cash', [PettyCashController::class, 'index'])->name('petty-cash.index');
    Route::get('/petty-cash/create', [PettyCashController::class, 'create'])->name('petty-cash.create');
    Route::post('/petty-cash', [PettyCashController::class, 'store'])->name('petty-cash.store');
    Route::get('/petty-cash/{pettyCash}/edit', [PettyCashController::class, 'edit'])->name('petty-cash.edit');
    Route::put('/petty-cash/{pettyCash}', [PettyCashController::class, 'update'])->name('petty-cash.update');
    Route::delete('/petty-cash-items/{item}', [PettyCashItemController::class, 'destroy']);
    Route::put('/petty-cash/{id}/submit', [PettyCashController::class, 'submit'])->name('petty-cash.submit');
    Route::get('petty-cash/{pettyCash}/view', [PettyCashController::class, 'view'])->name('petty-cash.view');


    Route::get('/audit/petty-cash', [AuditPettyCashController::class, 'index'])->name('audit.petty-cash.index');
    Route::get('/petty-cash/{pettyCash}/view', [AuditPettyCashController::class, 'view'])->name('petty-cash.view');

    Route::post('/audit/petty-cash/{pettyCash}/distribution', [AuditPettyCashController::class, 'storeDistribution'])->name('audit.petty-cash.distribution');
    
    Route::get('/bursar/petty-cash', [BursarPettycashController::class, 'index'])->name('bursar.petty-cash.index');
    Route::put('/bursar/petty-cash/{pettyCash}/release', [BursarPettycashController::class, 'release'])->name('bursar.petty-cash.release');
    Route::put('/bursar/cashAdvance/{pettyCash}/release', [BursarPettycashController::class, 'releaseCashAdvance'])->name('bursar.cashAdvance.release');


    Route::post('/petty-cash/{pettyCash}/remarks', [PettyCashApprovalController::class, 'auditRemarks'])->name('petty-cash.remarks');
    Route::post('/petty-cash/{pettyCash}/approve', [PettyCashApprovalController::class, 'auditApproval'])->name('petty-cash.approve');
    Route::post('/petty-cash/{pettyCash}/return', [PettyCashApprovalController::class, 'auditReturn'])->name('petty-cash.return');
    Route::post('/petty-cash/{pettyCash}/approveLiquidate', [PettyCashApprovalController::class, 'auditApprovalLiquidate'])->name('petty-cash.approveLiquidate');
    
    // Petty Cash Fund
    Route::get('/petty-cash/fund', [PettyCashController::class, 'pettyCashFund'])->name('petty-cash.fund.index');

    Route::post('/petty-cash/fund', [PettyCashController::class, 'storeFund'])->name('petty-cash.fund.store');
});




