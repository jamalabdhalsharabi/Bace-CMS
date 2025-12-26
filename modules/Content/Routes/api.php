<?php

use Illuminate\Support\Facades\Route;
use Modules\Content\Http\Controllers\Api\ArticleListingController;
use Modules\Content\Http\Controllers\Api\ArticleManagementController;
use Modules\Content\Http\Controllers\Api\ArticleSocialController;
use Modules\Content\Http\Controllers\Api\ArticleTaxonomyController;
use Modules\Content\Http\Controllers\Api\ArticleWorkflowController;

/*
|--------------------------------------------------------------------------
| Content Module API V1 Routes - Feature-Based Controllers
|--------------------------------------------------------------------------
|
| Clean Architecture routes with specialized controllers following
| Single Responsibility Principle.
|
*/

Route::prefix('api/v1/articles')->middleware(['api'])->name('api.v1.articles.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Article Listing Routes (Read-Only)
    |--------------------------------------------------------------------------
    */
    Route::get('/', [ArticleListingController::class, 'index'])->name('index');
    Route::get('/slug/{slug}', [ArticleListingController::class, 'showBySlug'])->name('slug');
    Route::get('/{id}', [ArticleListingController::class, 'show'])->name('show');
    Route::get('/{id}/analytics', [ArticleListingController::class, 'analytics'])->name('analytics');
    Route::get('/{id}/revisions', [ArticleListingController::class, 'revisions'])->name('revisions');

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth.api')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Article Management Routes (CRUD)
        |--------------------------------------------------------------------------
        */
        Route::post('/', [ArticleManagementController::class, 'store'])->name('store');
        Route::put('/{id}', [ArticleManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [ArticleManagementController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [ArticleManagementController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/restore', [ArticleManagementController::class, 'restore'])->name('restore');
        Route::post('/{id}/duplicate', [ArticleManagementController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/restore-revision', [ArticleManagementController::class, 'restoreRevision'])->name('restore-revision');

        /*
        |--------------------------------------------------------------------------
        | Article Workflow Routes
        |--------------------------------------------------------------------------
        */
        Route::post('/{id}/publish', [ArticleWorkflowController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [ArticleWorkflowController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/schedule', [ArticleWorkflowController::class, 'schedule'])->name('schedule');
        Route::post('/{id}/submit-review', [ArticleWorkflowController::class, 'submitForReview'])->name('submit-review');
        Route::post('/{id}/start-review', [ArticleWorkflowController::class, 'startReview'])->name('start-review');
        Route::post('/{id}/approve', [ArticleWorkflowController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [ArticleWorkflowController::class, 'reject'])->name('reject');
        Route::post('/{id}/archive', [ArticleWorkflowController::class, 'archive'])->name('archive');
        Route::post('/{id}/unarchive', [ArticleWorkflowController::class, 'unarchive'])->name('unarchive');
        Route::post('/{id}/pin', [ArticleWorkflowController::class, 'pin'])->name('pin');
        Route::post('/{id}/unpin', [ArticleWorkflowController::class, 'unpin'])->name('unpin');

        /*
        |--------------------------------------------------------------------------
        | Article Taxonomy Routes
        |--------------------------------------------------------------------------
        */
        Route::put('/{id}/categories', [ArticleTaxonomyController::class, 'syncCategories'])->name('sync-categories');
        Route::post('/{id}/tags', [ArticleTaxonomyController::class, 'addTags'])->name('add-tags');
        Route::delete('/{id}/tags', [ArticleTaxonomyController::class, 'removeTags'])->name('remove-tags');
        Route::post('/{id}/related', [ArticleTaxonomyController::class, 'attachRelated'])->name('attach-related');

        /*
        |--------------------------------------------------------------------------
        | Article Social Routes
        |--------------------------------------------------------------------------
        */
        Route::post('/{id}/share', [ArticleSocialController::class, 'shareOnSocial'])->name('share');
        Route::post('/{id}/newsletter', [ArticleSocialController::class, 'sendToNewsletter'])->name('newsletter');
        Route::post('/{id}/comments/enable', [ArticleSocialController::class, 'enableComments'])->name('enable-comments');
        Route::post('/{id}/comments/disable', [ArticleSocialController::class, 'disableComments'])->name('disable-comments');
        Route::post('/{id}/comments/close', [ArticleSocialController::class, 'closeComments'])->name('close-comments');
    });
});
