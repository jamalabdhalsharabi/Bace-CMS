<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\Api\ProjectController;

Route::prefix('api/v1/projects')->middleware(['api'])->name('api.projects.')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('index');
    Route::get('/featured', [ProjectController::class, 'featured'])->name('featured');
    Route::get('/{id}', [ProjectController::class, 'show'])->name('show');
    Route::get('/slug/{slug}', [ProjectController::class, 'showBySlug'])->name('slug');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::put('/{id}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/publish', [ProjectController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [ProjectController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [ProjectController::class, 'archive'])->name('archive');
        Route::post('/{id}/duplicate', [ProjectController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/feature', [ProjectController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [ProjectController::class, 'unfeature'])->name('unfeature');
    });
});
