<?php

use Illuminate\Support\Facades\Route;
use Modules\Taxonomy\Http\Controllers\Api\TaxonomyController;

Route::prefix('api/v1/taxonomies')->middleware(['api'])->name('api.v1.taxonomies.')->group(function () {
    // Public routes
    Route::get('/types', [TaxonomyController::class, 'types'])->name('types');
    Route::get('/{type}', [TaxonomyController::class, 'index'])->name('index');
    Route::get('/{type}/tree', [TaxonomyController::class, 'tree'])->name('tree');
    Route::get('/{type}/export', [TaxonomyController::class, 'export'])->name('export');
    Route::get('/{type}/slug/{slug}', [TaxonomyController::class, 'showBySlug'])->name('slug');
    Route::get('/item/{id}', [TaxonomyController::class, 'show'])->name('show');
    Route::get('/item/{id}/stats', [TaxonomyController::class, 'contentStats'])->name('stats');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [TaxonomyController::class, 'store'])->name('store');
        Route::put('/{id}', [TaxonomyController::class, 'update'])->name('update');
        Route::delete('/{id}', [TaxonomyController::class, 'destroy'])->name('destroy');

        // Translations
        Route::post('/{id}/translations', [TaxonomyController::class, 'createTranslation'])->name('create-translation');

        // Tree Operations
        Route::post('/{id}/move', [TaxonomyController::class, 'move'])->name('move');
        Route::post('/{id}/change-parent', [TaxonomyController::class, 'changeParent'])->name('change-parent');
        Route::post('/reorder', [TaxonomyController::class, 'reorder'])->name('reorder');
        Route::post('/merge', [TaxonomyController::class, 'merge'])->name('merge');

        // Status
        Route::post('/{id}/activate', [TaxonomyController::class, 'activate'])->name('activate');
        Route::post('/{id}/deactivate', [TaxonomyController::class, 'deactivate'])->name('deactivate');

        // Import/Export
        Route::post('/import', [TaxonomyController::class, 'import'])->name('import');

        // Type Management
        Route::post('/types', [TaxonomyController::class, 'createType'])->name('create-type');
        Route::put('/types/{typeId}', [TaxonomyController::class, 'updateType'])->name('update-type');
        Route::delete('/types/{typeId}', [TaxonomyController::class, 'destroyType'])->name('destroy-type');

        // Cleanup
        Route::post('/{type}/clean-empty', [TaxonomyController::class, 'cleanEmpty'])->name('clean-empty');
    });
});
