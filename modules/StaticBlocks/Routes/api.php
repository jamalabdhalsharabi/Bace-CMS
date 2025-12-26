<?php

use Illuminate\Support\Facades\Route;
use Modules\StaticBlocks\Http\Controllers\Api\StaticBlockController;

Route::prefix('api/v1/static-blocks')->middleware(['api'])->name('api.v1.static-blocks.')->group(function () {
    // Public routes
    Route::get('/', [StaticBlockController::class, 'index'])->name('index');
    Route::get('/identifier/{identifier}', [StaticBlockController::class, 'show'])->name('identifier');
    Route::get('/{id}', [StaticBlockController::class, 'show'])->name('show');
    Route::get('/{id}/preview', [StaticBlockController::class, 'preview'])->name('preview');

    Route::middleware('auth.api')->group(function () {
        // CRUD
        Route::post('/', [StaticBlockController::class, 'store'])->name('store');
        Route::put('/{id}', [StaticBlockController::class, 'update'])->name('update');
        Route::delete('/{id}', [StaticBlockController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [StaticBlockController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/restore', [StaticBlockController::class, 'restore'])->name('restore');

        // Workflow
        Route::post('/{id}/save-draft', [StaticBlockController::class, 'saveDraft'])->name('save-draft');
        Route::post('/{id}/publish', [StaticBlockController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [StaticBlockController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [StaticBlockController::class, 'archive'])->name('archive');
        Route::post('/{id}/duplicate', [StaticBlockController::class, 'duplicate'])->name('duplicate');

        // Translations
        Route::post('/{id}/translations', [StaticBlockController::class, 'createTranslation'])->name('create-translation');

        // Embedding
        Route::post('/{id}/embed', [StaticBlockController::class, 'embedInPage'])->name('embed');
        Route::post('/{id}/remove-from-page', [StaticBlockController::class, 'removeFromPage'])->name('remove-from-page');

        // Visibility
        Route::post('/{id}/visibility', [StaticBlockController::class, 'setVisibility'])->name('set-visibility');
        Route::post('/{id}/schedule-visibility', [StaticBlockController::class, 'scheduleVisibility'])->name('schedule-visibility');

        // Export/Import
        Route::get('/{id}/export', [StaticBlockController::class, 'export'])->name('export');
        Route::post('/import', [StaticBlockController::class, 'import'])->name('import');

        // Usage
        Route::get('/{id}/usages', [StaticBlockController::class, 'findUsages'])->name('usages');
    });
});
