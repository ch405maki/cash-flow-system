<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Api\DepartmentController;
use App\Http\Controllers\Api\AccessController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\SignatoryController;
use App\Http\Controllers\Api\PurchaseOrderController;
use App\Http\Controllers\Api\CanvasController;
use App\Http\Controllers\Api\ActivityLogController;
use App\Http\Controllers\Api\ProfilePictureController;

// web
use App\Http\Controllers\Web\DashboardController;

use App\Models\User;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Notifications\Notifiable;
use App\Notifications\WelcomeEmail;


Route::get('/', [AuthenticatedSessionController::class, 'create'])
        ->name('home');

// Dashboard Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});

// Purchase Order Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::get('/purchase-order/create', [PurchaseOrderController::class, 'create'])->name('purchase-order.create');
    
    Route::patch('/purchase-orders/{purchaseOrder}/status', [PurchaseOrderController::class, 'updateStatus'])->name('purchase-orders.status.update');
});

// Canvas Route
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/canvas/create', [CanvasController::class, 'create'])->name('canvas.create');
    Route::post('/canvas', [CanvasController::class, 'store'])->name('canvas.store');
    Route::get('/canvas', [CanvasController::class, 'index'])->name('canvas.index');

    Route::get('/canvas/approval', [CanvasController::class, 'approval'])->name('canvas.approval');
    Route::get('/canvas/{canvas}', [CanvasController::class, 'show'])->name('canvas.show');
    Route::get('/canvases/{canvas}/download', [CanvasController::class, 'downloadAll'])->name('canvas.download.all');
    Route::get('/canvases/{canvas}/download/{file}', [CanvasController::class, 'downloadFile'])->name('canvas.download.file');
    Route::patch('/canvas/{canvas}', [CanvasController::class, 'update'])->name('canvas.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/logs', [ActivityLogController::class, 'index'])->name('logs.index');
    Route::get('/logs/{modelType}/{modelId}', [ActivityLogController::class, 'forModel'])->name('logs.model');
});

// Add these with your other routes
Route::middleware(['auth'])->group(function () {
    Route::get('/notifications', [App\Http\Controllers\NotificationController::class, 'index'])->name('notifications.index');
    Route::patch('/notifications/{notification}/read', [App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::patch('/notifications/read-all', [App\Http\Controllers\NotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
    Route::delete('/notifications/{notification}', [App\Http\Controllers\NotificationController::class, 'destroy'])->name('notifications.destroy');
});

Route::get('/test-email', function () {
    $details = [
        'subject' => 'Test Email from RPV System',
        'body' => 'This is a test email to verify the SMTP configuration.',
    ];
    Mail::raw($details['body'], function ($message) use ($details) {
        $message->from('admin@arellanolaw.edu', 'Arellano Law Foundation')
        ->to('markmanuel0317@gmail.com')
        ->subject($details['subject']);
    });
    return 'Test email sent!';
});

require __DIR__.'/settings.php';
require __DIR__.'/configuration.php';
require __DIR__.'/auth.php';
require __DIR__.'/pettycash.php';
require __DIR__.'/vouchers.php';
require __DIR__.'/reports.php';
require __DIR__.'/request.php';
require __DIR__.'/notification.php';
