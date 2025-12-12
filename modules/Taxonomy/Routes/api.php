<?php

use Illuminate\Support\Facades\Route;
use Modules\Taxonomy\Http\Controllers\Api\TaxonomyController;

Route::prefix('api/v1')->middleware(['api'])->group(function () {
    Route::get('/taxonomy/types', [TaxonomyController::class, 'types'])->name('api.v1.taxonomy.types');
    Route::get('/taxonomy/{type}', [TaxonomyController::class, 'index'])->name('api.v1.taxonomy.index');
    Route::get('/taxonomy/{type}/tree', [TaxonomyController::class, 'tree'])->name('api.v1.taxonomy.tree');
    Route::get('/taxonomy/{type}/slug/{slug}', [TaxonomyController::class, 'showBySlug'])->name('api.v1.taxonomy.slug');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/taxonomy', [TaxonomyController::class, 'store'])->name('api.v1.taxonomy.store');
        Route::get('/taxonomy/item/{id}', [TaxonomyController::class, 'show'])->name('api.v1.taxonomy.show');
        Route::put('/taxonomy/{id}', [TaxonomyController::class, 'update'])->name('api.v1.taxonomy.update');
        Route::delete('/taxonomy/{id}', [TaxonomyController::class, 'destroy'])->name('api.v1.taxonomy.destroy');

        // Hierarchy
        Route::post('/taxonomy/reorder', [TaxonomyController::class, 'reorder'])->name('api.v1.taxonomy.reorder');
        Route::post('/taxonomy/{id}/move', [TaxonomyController::class, 'move'])->name('api.v1.taxonomy.move');

        // Status
        Route::post('/taxonomy/{id}/activate', [TaxonomyController::class, 'activate'])->name('api.v1.taxonomy.activate');
        Route::post('/taxonomy/{id}/deactivate', [TaxonomyController::class, 'deactivate'])->name('api.v1.taxonomy.deactivate');

        // Merge
        Route::post('/taxonomy/{id}/merge/{targetId}', [TaxonomyController::class, 'merge'])->name('api.v1.taxonomy.merge');

        // Bulk
        Route::post('/taxonomy/bulk/delete', [TaxonomyController::class, 'bulkDelete'])->name('api.v1.taxonomy.bulk-delete');
        Route::post('/taxonomy/bulk/move', [TaxonomyController::class, 'bulkMove'])->name('api.v1.taxonomy.bulk-move');

        // Import/Export
        Route::post('/taxonomy/{type}/import', [TaxonomyController::class, 'import'])->name('api.v1.taxonomy.import');
        Route::get('/taxonomy/{type}/export', [TaxonomyController::class, 'export'])->name('api.v1.taxonomy.export');

        // Stats
        Route::get('/taxonomy/{id}/stats', [TaxonomyController::class, 'getStats'])->name('api.v1.taxonomy.stats');

        // Missing: Taxonomy Types Management, Translations, Cleanup
        Route::post('/taxonomy/types', [TaxonomyController::class, 'createType'])->name('api.v1.taxonomy.create-type');
        Route::put('/taxonomy/types/{typeId}', [TaxonomyController::class, 'updateType'])->name('api.v1.taxonomy.update-type');
        Route::delete('/taxonomy/types/{typeId}', [TaxonomyController::class, 'deleteType'])->name('api.v1.taxonomy.delete-type');
        Route::post('/taxonomy/{id}/translations', [TaxonomyController::class, 'addTranslation'])->name('api.v1.taxonomy.add-translation');
        Route::post('/taxonomy/cleanup-empty', [TaxonomyController::class, 'cleanupEmpty'])->name('api.v1.taxonomy.cleanup-empty');
    });
});
