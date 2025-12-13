<?php

use Illuminate\Support\Facades\Route;
use Modules\Currency\Http\Controllers\Api\CurrencyController;

Route::prefix('api/v1/currencies')->middleware(['api'])->name('api.currencies.')->group(function () {
    // Public routes
    Route::get('/', [CurrencyController::class, 'index'])->name('index');
    Route::get('/all', [CurrencyController::class, 'all'])->name('all');
    Route::get('/supported', [CurrencyController::class, 'supported'])->name('supported');
    Route::post('/convert', [CurrencyController::class, 'convert'])->name('convert');
    Route::post('/format', [CurrencyController::class, 'format'])->name('format');
    Route::get('/{id}', [CurrencyController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [CurrencyController::class, 'store'])->name('store');
        Route::put('/{id}', [CurrencyController::class, 'update'])->name('update');
        Route::delete('/{id}', [CurrencyController::class, 'destroy'])->name('destroy');

        // Status
        Route::post('/{id}/activate', [CurrencyController::class, 'activate'])->name('activate');
        Route::post('/{id}/deactivate', [CurrencyController::class, 'deactivate'])->name('deactivate');
        Route::post('/{id}/set-default', [CurrencyController::class, 'setDefault'])->name('set-default');

        // Exchange Rates
        Route::put('/{id}/rate', [CurrencyController::class, 'updateRate'])->name('update-rate');
        Route::post('/sync-rates', [CurrencyController::class, 'syncRates'])->name('sync-rates');
    });
});
