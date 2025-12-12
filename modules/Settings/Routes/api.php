<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\Api\SettingsController;

Route::prefix('api/v1/settings')->middleware(['api'])->name('api.v1.settings.')->group(function () {
    Route::get('/public', [SettingsController::class, 'public'])->name('public');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [SettingsController::class, 'index'])->name('index');
        Route::get('/group/{group}', [SettingsController::class, 'group'])->name('group');
        Route::get('/{key}', [SettingsController::class, 'show'])->name('show');
        Route::put('/', [SettingsController::class, 'update'])->name('update');
        Route::delete('/{key}', [SettingsController::class, 'destroy'])->name('destroy');
        Route::post('/cache/clear', [SettingsController::class, 'clearCache'])->name('cache.clear');
    });
});
