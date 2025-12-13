<?php

use Illuminate\Support\Facades\Route;
use Modules\Currency\Http\Controllers\Api\CurrencyController;

Route::prefix('api/v1/currencies')->middleware(['api'])->name('api.currencies.')->group(function () {
    Route::get('/', [CurrencyController::class, 'index'])->name('index');
    Route::get('/active', [CurrencyController::class, 'active'])->name('active');
    Route::get('/{id}', [CurrencyController::class, 'show'])->name('show');
    Route::get('/convert', [CurrencyController::class, 'convert'])->name('convert');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [CurrencyController::class, 'store'])->name('store');
        Route::put('/{id}', [CurrencyController::class, 'update'])->name('update');
        Route::delete('/{id}', [CurrencyController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/set-default', [CurrencyController::class, 'setDefault'])->name('set-default');
        Route::put('/{id}/exchange-rate', [CurrencyController::class, 'updateExchangeRate'])->name('exchange-rate');
    });
});
