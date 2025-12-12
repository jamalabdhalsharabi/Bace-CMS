<?php

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\Api\SearchController;

Route::prefix('api/v1')->middleware(['api'])->group(function () {
    Route::post('/search', [SearchController::class, 'search'])->name('api.v1.search');
    Route::post('/search/{index}', [SearchController::class, 'searchIndex'])->name('api.v1.search.index');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('api.v1.search.suggestions');
});
