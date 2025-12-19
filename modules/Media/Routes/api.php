<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\Api\MediaController;
use Modules\Media\Http\Controllers\Api\FolderController;

Route::prefix('api/v1/media')->middleware(['api', 'auth:sanctum'])->name('api.v1.media.')->group(function () {
    // List & Search
    Route::get('/', [MediaController::class, 'index'])->name('index');
    Route::get('/search', [MediaController::class, 'search'])->name('search');
    Route::get('/stats', [MediaController::class, 'stats'])->name('stats');
    Route::get('/{id}', [MediaController::class, 'show'])->name('show');
    Route::get('/{id}/usage', [MediaController::class, 'analyzeUsage'])->name('usage');
    Route::get('/{id}/metadata', [MediaController::class, 'extractMetadata'])->name('metadata');

    // Upload
    Route::post('/', [MediaController::class, 'store'])->name('store');
    Route::post('/multiple', [MediaController::class, 'storeMultiple'])->name('store-multiple');
    Route::post('/chunk', [MediaController::class, 'uploadChunk'])->name('upload-chunk');

    // CRUD
    Route::put('/{id}', [MediaController::class, 'update'])->name('update');
    Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
    Route::delete('/{id}/force', [MediaController::class, 'forceDestroy'])->name('force-destroy');
    Route::post('/{id}/restore', [MediaController::class, 'restore'])->name('restore');
    Route::post('/{id}/replace', [MediaController::class, 'replace'])->name('replace');
    Route::post('/{id}/duplicate', [MediaController::class, 'duplicate'])->name('duplicate');
    Route::post('/{id}/move', [MediaController::class, 'move'])->name('move');

    // Image Processing
    Route::post('/{id}/optimize', [MediaController::class, 'optimize'])->name('optimize');
    Route::post('/{id}/crop', [MediaController::class, 'crop'])->name('crop');
    Route::post('/{id}/rotate', [MediaController::class, 'rotate'])->name('rotate');
    Route::post('/{id}/variants', [MediaController::class, 'generateVariants'])->name('generate-variants');
    Route::post('/{id}/regenerate-variants', [MediaController::class, 'regenerateVariants'])->name('regenerate-variants');

    // Temporary URL
    Route::post('/{id}/temporary-url', [MediaController::class, 'temporaryUrl'])->name('temporary-url');

    // Bulk Operations
    Route::post('/bulk-delete', [MediaController::class, 'bulkDestroy'])->name('bulk-delete');
    Route::post('/bulk-move', [MediaController::class, 'bulkMove'])->name('bulk-move');
    Route::post('/clean-unused', [MediaController::class, 'cleanUnused'])->name('clean-unused');
    Route::post('/remove-duplicates', [MediaController::class, 'removeDuplicates'])->name('remove-duplicates');
});

Route::prefix('api/v1/folders')->middleware(['api', 'auth:sanctum'])->name('api.v1.folders.')->group(function () {
    Route::get('/', [FolderController::class, 'index'])->name('index');
    Route::post('/', [FolderController::class, 'store'])->name('store');
    Route::delete('/{id}', [FolderController::class, 'destroy'])->name('destroy');
});
