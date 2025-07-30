<?php

use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Configuration\UserAccessController;
use App\Http\Controllers\Configuration\DepartmentController;
use App\Http\Controllers\Configuration\SignatoryController;
use App\Http\Controllers\Configuration\AccountController;
use App\Http\Controllers\Api\ProfilePictureController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/configuration/users', [UserController::class, 'index'])->name('users.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/configuration/users-access', [UserAccessController::class, 'index'])->name('users-access.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/configuration/departments', [DepartmentController::class, 'index'])->name('departments.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/configuration/signatory', [SignatoryController::class, 'index'])->name('signatory.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/configuration/account', [AccountController::class, 'index'])->name('account.index');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/configuration/profile-pictures', [ProfilePictureController::class, 'index'])->name('profile-pictures.index');
});
