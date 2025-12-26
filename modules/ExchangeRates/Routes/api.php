<?php

use Illuminate\Support\Facades\Route;
use Modules\ExchangeRates\Http\Controllers\Api\ExchangeRateController;

Route::prefix('api/v1/exchange-rates')->middleware(['api'])->name('api.v1.exchange-rates.')->group(function () {
    Route::get('/', [ExchangeRateController::class, 'index'])->name('index');
    Route::get('/{baseId}/{targetId}', [ExchangeRateController::class, 'show'])->name('show');
    Route::post('/convert', [ExchangeRateController::class, 'convert'])->name('convert');

    Route::middleware('auth.api')->group(function () {
        Route::post('/fetch', [ExchangeRateController::class, 'fetch'])->name('fetch');
        Route::put('/', [ExchangeRateController::class, 'update'])->name('update');
        Route::post('/{id}/freeze', [ExchangeRateController::class, 'freeze'])->name('freeze');
        Route::post('/{id}/unfreeze', [ExchangeRateController::class, 'unfreeze'])->name('unfreeze');

        Route::get('/{baseId}/{targetId}/history', [ExchangeRateController::class, 'history'])->name('history');
        Route::delete('/history/clean', [ExchangeRateController::class, 'cleanHistory'])->name('history.clean');
        Route::post('/history/import', [ExchangeRateController::class, 'importHistory'])->name('history.import');
        Route::get('/{baseId}/{targetId}/export', [ExchangeRateController::class, 'exportHistory'])->name('history.export');

        Route::post('/alerts', [ExchangeRateController::class, 'createAlert'])->name('alerts.create');
        Route::post('/alerts/{id}/deactivate', [ExchangeRateController::class, 'deactivateAlert'])->name('alerts.deactivate');

        Route::get('/conflicts', [ExchangeRateController::class, 'detectConflicts'])->name('conflicts');
    });
});
