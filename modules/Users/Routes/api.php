<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\Api\UserController;
use Modules\Users\Http\Controllers\Api\ProfileController;

Route::prefix('api/v1/users')->middleware(['api', 'auth:sanctum'])->name('api.users.')->group(function () {
    Route::get('/', [UserController::class, 'index'])->name('index');
    Route::post('/', [UserController::class, 'store'])->name('store');
    Route::get('/{id}', [UserController::class, 'show'])->name('show');
    Route::put('/{id}', [UserController::class, 'update'])->name('update');
    Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');

    Route::post('/{id}/activate', [UserController::class, 'activate'])->name('activate');
    Route::post('/{id}/suspend', [UserController::class, 'suspend'])->name('suspend');
    Route::post('/{id}/change-password', [UserController::class, 'changePassword'])->name('change-password');
});

Route::prefix('api/v1/profile')->middleware(['api', 'auth:sanctum'])->name('api.profile.')->group(function () {
    Route::get('/', [ProfileController::class, 'show'])->name('show');
    Route::put('/', [ProfileController::class, 'update'])->name('update');
    Route::post('/avatar', [ProfileController::class, 'updateAvatar'])->name('avatar.update');
    Route::delete('/avatar', [ProfileController::class, 'removeAvatar'])->name('avatar.remove');
});
