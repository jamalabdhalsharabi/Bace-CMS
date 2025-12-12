<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\Api\MediaController;

Route::prefix('api/v1')->middleware(['api', 'auth:sanctum'])->group(function () {
    Route::prefix('media')->name('api.v1.media.')->group(function () {
        // CRUD
        Route::get('/', [MediaController::class, 'index'])->name('index');
        Route::post('/', [MediaController::class, 'store'])->name('store');
        Route::post('/bulk', [MediaController::class, 'storeMultiple'])->name('store.multiple');
        Route::post('/url', [MediaController::class, 'uploadFromUrl'])->name('upload-url');
        Route::get('/{id}', [MediaController::class, 'show'])->name('show');
        Route::put('/{id}', [MediaController::class, 'update'])->name('update');
        Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [MediaController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/restore', [MediaController::class, 'restore'])->name('restore');

        // Organization
        Route::post('/{id}/move', [MediaController::class, 'move'])->name('move');
        Route::post('/bulk/move', [MediaController::class, 'bulkMove'])->name('bulk-move');

        // Processing
        Route::post('/{id}/regenerate', [MediaController::class, 'regenerateConversions'])->name('regenerate');
        Route::post('/{id}/crop', [MediaController::class, 'crop'])->name('crop');
        Route::post('/{id}/resize', [MediaController::class, 'resize'])->name('resize');
        Route::post('/{id}/optimize', [MediaController::class, 'optimize'])->name('optimize');
        Route::post('/{id}/variants', [MediaController::class, 'createVariant'])->name('create-variant');

        // Metadata
        Route::put('/{id}/alt', [MediaController::class, 'updateAlt'])->name('update-alt');
        Route::put('/{id}/caption', [MediaController::class, 'updateCaption'])->name('update-caption');
        Route::get('/{id}/metadata', [MediaController::class, 'extractMetadata'])->name('metadata');

        // Other
        Route::post('/{id}/duplicate', [MediaController::class, 'duplicate'])->name('duplicate');
        Route::get('/{id}/usage', [MediaController::class, 'getUsage'])->name('usage');
        Route::post('/{id}/replace', [MediaController::class, 'replaceFile'])->name('replace');
        Route::get('/{id}/download', [MediaController::class, 'download'])->name('download');
    });

    // Folders
    Route::prefix('media-folders')->name('api.v1.media-folders.')->group(function () {
        Route::get('/', [MediaController::class, 'folders'])->name('index');
        Route::post('/', [MediaController::class, 'createFolder'])->name('store');
        Route::put('/{id}', [MediaController::class, 'renameFolder'])->name('update');
        Route::delete('/{id}', [MediaController::class, 'deleteFolder'])->name('destroy');
        Route::post('/{id}/move', [MediaController::class, 'moveFolder'])->name('move');
    });

    // Missing Media Operations
    Route::prefix('media')->name('api.v1.media.')->group(function () {
        Route::post('/bulk/delete', [MediaController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/cleanup-unused', [MediaController::class, 'cleanupUnused'])->name('cleanup-unused');
        Route::post('/deduplicate', [MediaController::class, 'deduplicate'])->name('deduplicate');
        Route::post('/{id}/rotate', [MediaController::class, 'rotate'])->name('rotate');
    });
});
