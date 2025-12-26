<?php

use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\Api\AuthenticationController;
use Modules\Auth\Http\Controllers\Api\PasswordController;
use Modules\Auth\Http\Controllers\Api\PasswordResetController;
use Modules\Auth\Http\Controllers\Api\RegistrationController;

Route::prefix('api/v1/auth')->middleware(['api'])->name('api.v1.auth.')->group(function () {
    // Registration routes
    Route::post('/register', [RegistrationController::class, 'register'])->name('register');
    
    // Authentication routes
    Route::post('/login', [AuthenticationController::class, 'login'])->name('login');
    
    // Password reset routes
    Route::post('/forgot-password', [PasswordResetController::class, 'forgotPassword'])->name('password.forgot');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.reset');

    // Protected routes
    Route::middleware('auth.api')->group(function () {
        Route::get('/me', [AuthenticationController::class, 'me'])->name('me');
        Route::post('/logout', [AuthenticationController::class, 'logout'])->name('logout');
        Route::post('/change-password', [PasswordController::class, 'change'])->name('password.change');
    });
});
