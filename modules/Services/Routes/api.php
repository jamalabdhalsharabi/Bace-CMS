<?php

use Illuminate\Support\Facades\Route;
use Modules\Services\Http\Controllers\Api\ServiceController;

Route::prefix('api/v1/services')->middleware(['api'])->name('api.services.')->group(function () {
    Route::get('/', [ServiceController::class, 'index'])->name('index');
    Route::get('/{id}', [ServiceController::class, 'show'])->name('show');
    Route::get('/slug/{slug}', [ServiceController::class, 'showBySlug'])->name('slug');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [ServiceController::class, 'store'])->name('store');
        Route::put('/{id}', [ServiceController::class, 'update'])->name('update');
        Route::delete('/{id}', [ServiceController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/publish', [ServiceController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [ServiceController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [ServiceController::class, 'archive'])->name('archive');
        Route::post('/{id}/duplicate', [ServiceController::class, 'duplicate'])->name('duplicate');
        Route::post('/reorder', [ServiceController::class, 'reorder'])->name('reorder');
    });
});
