<?php

use Illuminate\Support\Facades\Route;
use Modules\Settings\Http\Controllers\Api\SettingController;

Route::prefix('api/v1/settings')->middleware(['api'])->name('api.v1.settings.')->group(function () {
    Route::get('/public', [SettingController::class, 'publicSettings'])->name('public');

    Route::middleware('auth.api')->group(function () {
        Route::get('/', [SettingController::class, 'index'])->name('index');
        Route::get('/group/{group}', [SettingController::class, 'byGroup'])->name('group');
        Route::get('/{key}', [SettingController::class, 'show'])->name('show');
        Route::put('/{key}', [SettingController::class, 'update'])->name('update');
        Route::post('/bulk', [SettingController::class, 'bulkUpdate'])->name('bulk');
        Route::delete('/{key}', [SettingController::class, 'destroy'])->name('destroy');
    });
});
