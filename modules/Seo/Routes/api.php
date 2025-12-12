<?php

use Illuminate\Support\Facades\Route;
use Modules\Seo\Http\Controllers\Api\SeoController;

Route::prefix('api/v1/seo')->middleware(['api', 'auth:sanctum'])->name('api.v1.seo.')->group(function () {
    Route::get('/meta', [SeoController::class, 'getSeoMeta'])->name('meta.get');
    Route::post('/meta', [SeoController::class, 'saveSeoMeta'])->name('meta.save');

    Route::get('/redirects', [SeoController::class, 'redirects'])->name('redirects.index');
    Route::post('/redirects', [SeoController::class, 'storeRedirect'])->name('redirects.store');
    Route::put('/redirects/{id}', [SeoController::class, 'updateRedirect'])->name('redirects.update');
    Route::delete('/redirects/{id}', [SeoController::class, 'destroyRedirect'])->name('redirects.destroy');
});
