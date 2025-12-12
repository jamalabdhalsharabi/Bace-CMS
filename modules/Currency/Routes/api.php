<?php

use Illuminate\Support\Facades\Route;
use Modules\Currency\Http\Controllers\Api\CurrencyController;

Route::prefix('api/v1')->middleware(['api'])->group(function () {
    Route::get('/currencies', [CurrencyController::class, 'index'])->name('api.v1.currencies.index');
    Route::post('/currencies/convert', [CurrencyController::class, 'convert'])->name('api.v1.currencies.convert');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/currencies', [CurrencyController::class, 'store'])->name('api.v1.currencies.store');
        Route::get('/currencies/{id}', [CurrencyController::class, 'show'])->name('api.v1.currencies.show');
        Route::put('/currencies/{id}', [CurrencyController::class, 'update'])->name('api.v1.currencies.update');
        Route::delete('/currencies/{id}', [CurrencyController::class, 'destroy'])->name('api.v1.currencies.destroy');

        // Status
        Route::post('/currencies/{id}/activate', [CurrencyController::class, 'activate'])->name('api.v1.currencies.activate');
        Route::post('/currencies/{id}/deactivate', [CurrencyController::class, 'deactivate'])->name('api.v1.currencies.deactivate');
        Route::post('/currencies/{id}/default', [CurrencyController::class, 'setDefault'])->name('api.v1.currencies.set-default');

        // Format
        Route::put('/currencies/{id}/format', [CurrencyController::class, 'updateFormat'])->name('api.v1.currencies.update-format');

        // Order
        Route::post('/currencies/reorder', [CurrencyController::class, 'reorder'])->name('api.v1.currencies.reorder');

        // Sync
        Route::post('/currencies/sync-symbols', [CurrencyController::class, 'syncSymbols'])->name('api.v1.currencies.sync-symbols');

        // Missing: ISO Import, Payment Gateway Sync, Product Impact
        Route::post('/currencies/import-iso', [CurrencyController::class, 'importIso'])->name('api.v1.currencies.import-iso');
        Route::post('/currencies/sync-payment-gateways', [CurrencyController::class, 'syncPaymentGateways'])->name('api.v1.currencies.sync-gateways');
        Route::post('/currencies/{id}/update-products', [CurrencyController::class, 'updateProductsImpact'])->name('api.v1.currencies.update-products');
    });
});
