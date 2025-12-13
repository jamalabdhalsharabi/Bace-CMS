<?php

use Illuminate\Support\Facades\Route;
use Modules\Content\Http\Controllers\Api\ArticleControllerV2;

/*
|--------------------------------------------------------------------------
| Content Module API V2 Routes
|--------------------------------------------------------------------------
|
| Clean Architecture routes using specialized services.
|
*/

Route::prefix('api/v2')->middleware(['api'])->group(function () {
    // Public routes
    Route::get('/articles', [ArticleControllerV2::class, 'index'])->name('api.v2.articles.index');
    Route::get('/articles/slug/{slug}', [ArticleControllerV2::class, 'showBySlug'])->name('api.v2.articles.slug');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Articles CRUD
        Route::post('/articles', [ArticleControllerV2::class, 'store'])->name('api.v2.articles.store');
        Route::get('/articles/{id}', [ArticleControllerV2::class, 'show'])->name('api.v2.articles.show');
        Route::put('/articles/{id}', [ArticleControllerV2::class, 'update'])->name('api.v2.articles.update');
        Route::delete('/articles/{id}', [ArticleControllerV2::class, 'destroy'])->name('api.v2.articles.destroy');
        Route::post('/articles/{id}/restore', [ArticleControllerV2::class, 'restore'])->name('api.v2.articles.restore');

        // Articles Workflow
        Route::post('/articles/{id}/submit-review', [ArticleControllerV2::class, 'submitForReview'])->name('api.v2.articles.submit-review');
        Route::post('/articles/{id}/start-review', [ArticleControllerV2::class, 'startReview'])->name('api.v2.articles.start-review');
        Route::post('/articles/{id}/approve', [ArticleControllerV2::class, 'approve'])->name('api.v2.articles.approve');
        Route::post('/articles/{id}/reject', [ArticleControllerV2::class, 'reject'])->name('api.v2.articles.reject');
        Route::post('/articles/{id}/publish', [ArticleControllerV2::class, 'publish'])->name('api.v2.articles.publish');
        Route::post('/articles/{id}/unpublish', [ArticleControllerV2::class, 'unpublish'])->name('api.v2.articles.unpublish');
        Route::post('/articles/{id}/schedule', [ArticleControllerV2::class, 'schedule'])->name('api.v2.articles.schedule');
        Route::post('/articles/{id}/archive', [ArticleControllerV2::class, 'archive'])->name('api.v2.articles.archive');
        Route::post('/articles/{id}/unarchive', [ArticleControllerV2::class, 'unarchive'])->name('api.v2.articles.unarchive');

        // Articles Features
        Route::post('/articles/{id}/duplicate', [ArticleControllerV2::class, 'duplicate'])->name('api.v2.articles.duplicate');

        // Articles Taxonomy
        Route::put('/articles/{id}/categories', [ArticleControllerV2::class, 'syncCategories'])->name('api.v2.articles.categories');
        Route::post('/articles/{id}/tags', [ArticleControllerV2::class, 'addTags'])->name('api.v2.articles.add-tags');

        // Articles Comments
        Route::post('/articles/{id}/comments/enable', [ArticleControllerV2::class, 'enableComments'])->name('api.v2.articles.enable-comments');
        Route::post('/articles/{id}/comments/disable', [ArticleControllerV2::class, 'disableComments'])->name('api.v2.articles.disable-comments');
    });
});
