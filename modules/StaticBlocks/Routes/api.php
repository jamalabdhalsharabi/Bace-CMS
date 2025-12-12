<?php

use Illuminate\Support\Facades\Route;
use Modules\StaticBlocks\Http\Controllers\Api\StaticBlockController;

Route::prefix('api/v1/blocks')->middleware(['api'])->name('api.v1.blocks.')->group(function () {
    Route::get('/', [StaticBlockController::class, 'index'])->name('index');
    Route::get('/{identifier}', [StaticBlockController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [StaticBlockController::class, 'store'])->name('store');
        Route::put('/{id}', [StaticBlockController::class, 'update'])->name('update');
        Route::delete('/{id}', [StaticBlockController::class, 'destroy'])->name('destroy');

        // Workflow
        Route::post('/{id}/publish', [StaticBlockController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [StaticBlockController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [StaticBlockController::class, 'archive'])->name('archive');

        // Scheduling
        Route::post('/{id}/schedule', [StaticBlockController::class, 'schedule'])->name('schedule');
        Route::post('/{id}/cancel-schedule', [StaticBlockController::class, 'cancelSchedule'])->name('cancel-schedule');

        // Visibility
        Route::put('/{id}/visibility', [StaticBlockController::class, 'setVisibilityRules'])->name('visibility');

        // Translations
        Route::post('/{id}/translations', [StaticBlockController::class, 'addTranslation'])->name('add-translation');
        Route::put('/{id}/translations/{locale}', [StaticBlockController::class, 'updateTranslation'])->name('update-translation');
        Route::delete('/{id}/translations/{locale}', [StaticBlockController::class, 'deleteTranslation'])->name('delete-translation');

        // Clone & Revisions
        Route::post('/{id}/duplicate', [StaticBlockController::class, 'duplicate'])->name('duplicate');
        Route::get('/{id}/revisions', [StaticBlockController::class, 'revisions'])->name('revisions');
        Route::post('/{id}/revisions/restore', [StaticBlockController::class, 'restoreRevision'])->name('restore-revision');

        // Media
        Route::put('/{id}/media', [StaticBlockController::class, 'attachMedia'])->name('attach-media');

        // Missing: Embed, Preview, Export, Import, Find Usages
        Route::post('/{id}/embed', [StaticBlockController::class, 'embedInPage'])->name('embed');
        Route::delete('/{id}/embed', [StaticBlockController::class, 'removeFromPage'])->name('remove-embed');
        Route::get('/{id}/preview', [StaticBlockController::class, 'preview'])->name('preview');
        Route::get('/{id}/usages', [StaticBlockController::class, 'findUsages'])->name('usages');
        Route::post('/import', [StaticBlockController::class, 'import'])->name('import');
        Route::get('/export', [StaticBlockController::class, 'export'])->name('export');
    });
});
