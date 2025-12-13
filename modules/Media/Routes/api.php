<?php

use Illuminate\Support\Facades\Route;
use Modules\Media\Http\Controllers\Api\MediaController;
use Modules\Media\Http\Controllers\Api\FolderController;

Route::prefix('api/v1/media')->middleware(['api', 'auth:sanctum'])->name('api.media.')->group(function () {
    Route::get('/', [MediaController::class, 'index'])->name('index');
    Route::post('/upload', [MediaController::class, 'upload'])->name('upload');
    Route::get('/{id}', [MediaController::class, 'show'])->name('show');
    Route::put('/{id}', [MediaController::class, 'update'])->name('update');
    Route::delete('/{id}', [MediaController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/move', [MediaController::class, 'move'])->name('move');
    Route::post('/{id}/duplicate', [MediaController::class, 'duplicate'])->name('duplicate');
    Route::post('/bulk-move', [MediaController::class, 'bulkMove'])->name('bulk-move');
});

Route::prefix('api/v1/folders')->middleware(['api', 'auth:sanctum'])->name('api.folders.')->group(function () {
    Route::get('/', [FolderController::class, 'index'])->name('index');
    Route::post('/', [FolderController::class, 'store'])->name('store');
    Route::delete('/{id}', [FolderController::class, 'destroy'])->name('destroy');
});
