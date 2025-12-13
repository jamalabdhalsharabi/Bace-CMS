<?php

use Illuminate\Support\Facades\Route;
use Modules\Taxonomy\Http\Controllers\Api\TaxonomyController;

Route::prefix('api/v1/taxonomies')->middleware(['api'])->name('api.taxonomies.')->group(function () {
    Route::get('/', [TaxonomyController::class, 'index'])->name('index');
    Route::get('/tree', [TaxonomyController::class, 'tree'])->name('tree');
    Route::get('/{id}', [TaxonomyController::class, 'show'])->name('show');
    Route::get('/slug/{slug}', [TaxonomyController::class, 'showBySlug'])->name('slug');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [TaxonomyController::class, 'store'])->name('store');
        Route::put('/{id}', [TaxonomyController::class, 'update'])->name('update');
        Route::delete('/{id}', [TaxonomyController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [TaxonomyController::class, 'reorder'])->name('reorder');
        Route::post('/{id}/move', [TaxonomyController::class, 'move'])->name('move');
    });
});
