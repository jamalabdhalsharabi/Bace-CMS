<?php

use Illuminate\Support\Facades\Route;
use Modules\Seo\Http\Controllers\Api\SeoController;

Route::prefix('api/v1/seo')->middleware(['api'])->name('api.seo.')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/meta/{entityType}/{entityId}', [SeoController::class, 'getMeta'])->name('meta.show');
        Route::put('/meta/{entityType}/{entityId}', [SeoController::class, 'updateMeta'])->name('meta.update');

        Route::get('/redirects', [SeoController::class, 'redirects'])->name('redirects.index');
        Route::post('/redirects', [SeoController::class, 'createRedirect'])->name('redirects.store');
        Route::delete('/redirects/{id}', [SeoController::class, 'deleteRedirect'])->name('redirects.destroy');

        Route::get('/stats/page-views', [SeoController::class, 'pageViewStats'])->name('stats.page-views');
        Route::get('/stats/searches', [SeoController::class, 'searchStats'])->name('stats.searches');
    });
});
