<?php

use Illuminate\Support\Facades\Route;
use Modules\ExchangeRates\Http\Controllers\Api\ExchangeRateController;

Route::prefix('api/v1')->middleware(['api'])->group(function () {
    // Public
    Route::get('/exchange-rates', [ExchangeRateController::class, 'index'])->name('api.v1.exchange-rates.index');
    Route::get('/exchange-rates/{baseId}/{targetId}', [ExchangeRateController::class, 'show'])->name('api.v1.exchange-rates.show');
    Route::post('/exchange-rates/convert', [ExchangeRateController::class, 'convert'])->name('api.v1.exchange-rates.convert');

    Route::middleware('auth:sanctum')->group(function () {
        // Fetch & Update
        Route::post('/admin/exchange-rates/fetch', [ExchangeRateController::class, 'fetch'])->name('api.v1.exchange-rates.fetch');
        Route::put('/admin/exchange-rates', [ExchangeRateController::class, 'update'])->name('api.v1.exchange-rates.update');
        
        // Freeze
        Route::post('/admin/exchange-rates/{id}/freeze', [ExchangeRateController::class, 'freeze'])->name('api.v1.exchange-rates.freeze');
        Route::post('/admin/exchange-rates/{id}/unfreeze', [ExchangeRateController::class, 'unfreeze'])->name('api.v1.exchange-rates.unfreeze');
        
        // History
        Route::get('/admin/exchange-rates/{baseId}/{targetId}/history', [ExchangeRateController::class, 'history'])->name('api.v1.exchange-rates.history');
        Route::delete('/admin/exchange-rates/history/clean', [ExchangeRateController::class, 'cleanHistory'])->name('api.v1.exchange-rates.clean-history');
        Route::post('/admin/exchange-rates/history/import', [ExchangeRateController::class, 'importHistory'])->name('api.v1.exchange-rates.import-history');
        Route::get('/admin/exchange-rates/{baseId}/{targetId}/history/export', [ExchangeRateController::class, 'exportHistory'])->name('api.v1.exchange-rates.export-history');
        
        // Alerts
        Route::post('/admin/exchange-rates/alerts', [ExchangeRateController::class, 'createAlert'])->name('api.v1.exchange-rates.create-alert');
        Route::post('/admin/exchange-rates/alerts/{id}/deactivate', [ExchangeRateController::class, 'deactivateAlert'])->name('api.v1.exchange-rates.deactivate-alert');
        
        // Conflicts & Impact
        Route::get('/admin/exchange-rates/conflicts', [ExchangeRateController::class, 'detectConflicts'])->name('api.v1.exchange-rates.conflicts');
        Route::post('/admin/exchange-rates/update-products', [ExchangeRateController::class, 'updateProductPrices'])->name('api.v1.exchange-rates.update-products');
    });
});
