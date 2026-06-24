<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\RequestToOrderController;

Route::prefix('request-to-order')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [RequestToOrderController::class, 'index'])->name('request-to-order.index');
    Route::get('/create', [RequestToOrderController::class, 'create'])->name('request-to-order.create');
    Route::get('/list-to-order', [RequestToOrderController::class, 'list'])->name('request-to-order.list-to-order');
    Route::get('/{order}', [RequestToOrderController::class, 'show'])->name('request-to-order.show');
    Route::get('/{order}/release', [RequestToOrderController::class, 'releaseCreate'])->name('request-to-order.release.create');
});
