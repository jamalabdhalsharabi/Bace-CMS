<?php

use Illuminate\Support\Facades\Route;
use Modules\Users\Http\Controllers\Api\UserController;

Route::prefix('api/v1')->middleware(['api'])->group(function () {
    Route::prefix('users')->name('api.v1.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{id}', [UserController::class, 'show'])->name('show');
        Route::put('/{id}', [UserController::class, 'update'])->name('update');
        Route::delete('/{id}', [UserController::class, 'destroy'])->name('destroy');

        Route::post('/{id}/activate', [UserController::class, 'activate'])->name('activate');
        Route::post('/{id}/suspend', [UserController::class, 'suspend'])->name('suspend');
    });
});
