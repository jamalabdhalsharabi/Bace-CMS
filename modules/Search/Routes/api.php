<?php

use Illuminate\Support\Facades\Route;
use Modules\Search\Http\Controllers\Api\SearchController;

Route::prefix('api/v1/search')->middleware(['api'])->name('api.search.')->group(function () {
    Route::get('/', [SearchController::class, 'search'])->name('index');
    Route::get('/suggestions', [SearchController::class, 'suggestions'])->name('suggestions');
});
