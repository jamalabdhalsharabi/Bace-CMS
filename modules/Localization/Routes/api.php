<?php

use Illuminate\Support\Facades\Route;
use Modules\Localization\Http\Controllers\Api\LanguageController;

Route::prefix('api/v1/languages')->middleware(['api'])->name('api.languages.')->group(function () {
    Route::get('/', [LanguageController::class, 'index'])->name('index');
    Route::get('/active', [LanguageController::class, 'active'])->name('active');
    Route::get('/{id}', [LanguageController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [LanguageController::class, 'store'])->name('store');
        Route::put('/{id}', [LanguageController::class, 'update'])->name('update');
        Route::delete('/{id}', [LanguageController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/set-default', [LanguageController::class, 'setDefault'])->name('set-default');
        Route::post('/{id}/activate', [LanguageController::class, 'activate'])->name('activate');
        Route::post('/{id}/deactivate', [LanguageController::class, 'deactivate'])->name('deactivate');
    });
});
