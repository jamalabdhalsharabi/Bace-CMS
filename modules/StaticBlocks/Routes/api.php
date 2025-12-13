<?php

use Illuminate\Support\Facades\Route;
use Modules\StaticBlocks\Http\Controllers\Api\StaticBlockController;

Route::prefix('api/v1/static-blocks')->middleware(['api'])->name('api.static-blocks.')->group(function () {
    Route::get('/', [StaticBlockController::class, 'index'])->name('index');
    Route::get('/identifier/{identifier}', [StaticBlockController::class, 'showByIdentifier'])->name('identifier');
    Route::get('/{id}', [StaticBlockController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [StaticBlockController::class, 'store'])->name('store');
        Route::put('/{id}', [StaticBlockController::class, 'update'])->name('update');
        Route::delete('/{id}', [StaticBlockController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/activate', [StaticBlockController::class, 'activate'])->name('activate');
        Route::post('/{id}/deactivate', [StaticBlockController::class, 'deactivate'])->name('deactivate');
    });
});
