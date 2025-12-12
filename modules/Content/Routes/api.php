<?php

use Illuminate\Support\Facades\Route;
use Modules\Content\Http\Controllers\Api\ArticleController;
use Modules\Content\Http\Controllers\Api\PageController;

Route::prefix('api/v1')->middleware(['api'])->group(function () {
    // Public routes
    Route::get('/articles', [ArticleController::class, 'index'])->name('api.v1.articles.index');
    Route::get('/articles/slug/{slug}', [ArticleController::class, 'showBySlug'])->name('api.v1.articles.slug');
    Route::get('/pages', [PageController::class, 'index'])->name('api.v1.pages.index');
    Route::get('/pages/tree', [PageController::class, 'tree'])->name('api.v1.pages.tree');
    Route::get('/pages/slug/{slug}', [PageController::class, 'showBySlug'])->name('api.v1.pages.slug');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // Articles CRUD
        Route::post('/articles', [ArticleController::class, 'store'])->name('api.v1.articles.store');
        Route::get('/articles/{id}', [ArticleController::class, 'show'])->name('api.v1.articles.show');
        Route::put('/articles/{id}', [ArticleController::class, 'update'])->name('api.v1.articles.update');
        Route::delete('/articles/{id}', [ArticleController::class, 'destroy'])->name('api.v1.articles.destroy');
        Route::delete('/articles/{id}/force', [ArticleController::class, 'forceDestroy'])->name('api.v1.articles.force-destroy');
        Route::post('/articles/{id}/restore', [ArticleController::class, 'restore'])->name('api.v1.articles.restore');

        // Articles Workflow
        Route::post('/articles/{id}/submit-review', [ArticleController::class, 'submitForReview'])->name('api.v1.articles.submit-review');
        Route::post('/articles/{id}/start-review', [ArticleController::class, 'startReview'])->name('api.v1.articles.start-review');
        Route::post('/articles/{id}/approve', [ArticleController::class, 'approve'])->name('api.v1.articles.approve');
        Route::post('/articles/{id}/reject', [ArticleController::class, 'reject'])->name('api.v1.articles.reject');
        Route::post('/articles/{id}/publish', [ArticleController::class, 'publish'])->name('api.v1.articles.publish');
        Route::post('/articles/{id}/unpublish', [ArticleController::class, 'unpublish'])->name('api.v1.articles.unpublish');
        Route::post('/articles/{id}/schedule', [ArticleController::class, 'schedule'])->name('api.v1.articles.schedule');
        Route::post('/articles/{id}/archive', [ArticleController::class, 'archive'])->name('api.v1.articles.archive');
        Route::post('/articles/{id}/unarchive', [ArticleController::class, 'unarchive'])->name('api.v1.articles.unarchive');

        // Articles Features
        Route::post('/articles/{id}/duplicate', [ArticleController::class, 'duplicate'])->name('api.v1.articles.duplicate');
        Route::post('/articles/{id}/pin', [ArticleController::class, 'pin'])->name('api.v1.articles.pin');
        Route::post('/articles/{id}/unpin', [ArticleController::class, 'unpin'])->name('api.v1.articles.unpin');

        // Articles Categories & Tags
        Route::put('/articles/{id}/categories', [ArticleController::class, 'syncCategories'])->name('api.v1.articles.categories');
        Route::post('/articles/{id}/tags', [ArticleController::class, 'addTags'])->name('api.v1.articles.add-tags');
        Route::delete('/articles/{id}/tags', [ArticleController::class, 'removeTags'])->name('api.v1.articles.remove-tags');
        Route::put('/articles/{id}/related', [ArticleController::class, 'attachRelated'])->name('api.v1.articles.related');

        // Articles Comments
        Route::post('/articles/{id}/comments/enable', [ArticleController::class, 'enableComments'])->name('api.v1.articles.enable-comments');
        Route::post('/articles/{id}/comments/disable', [ArticleController::class, 'disableComments'])->name('api.v1.articles.disable-comments');
        Route::post('/articles/{id}/comments/close', [ArticleController::class, 'closeComments'])->name('api.v1.articles.close-comments');

        // Articles Revisions
        Route::get('/articles/{id}/revisions', [ArticleController::class, 'revisions'])->name('api.v1.articles.revisions');
        Route::post('/articles/{id}/revisions/restore', [ArticleController::class, 'restoreRevision'])->name('api.v1.articles.restore-revision');

        // Articles Social & Analytics
        Route::post('/articles/{id}/share', [ArticleController::class, 'shareOnSocial'])->name('api.v1.articles.share');
        Route::post('/articles/{id}/newsletter', [ArticleController::class, 'sendToNewsletter'])->name('api.v1.articles.newsletter');
        Route::get('/articles/{id}/analytics', [ArticleController::class, 'analytics'])->name('api.v1.articles.analytics');

        // Pages CRUD
        Route::post('/pages', [PageController::class, 'store'])->name('api.v1.pages.store');
        Route::get('/pages/{id}', [PageController::class, 'show'])->name('api.v1.pages.show');
        Route::put('/pages/{id}', [PageController::class, 'update'])->name('api.v1.pages.update');
        Route::delete('/pages/{id}', [PageController::class, 'destroy'])->name('api.v1.pages.destroy');
        Route::delete('/pages/{id}/force', [PageController::class, 'forceDestroy'])->name('api.v1.pages.force-destroy');
        Route::post('/pages/{id}/restore', [PageController::class, 'restore'])->name('api.v1.pages.restore');

        // Pages Workflow
        Route::post('/pages/{id}/draft', [PageController::class, 'saveDraft'])->name('api.v1.pages.draft');
        Route::post('/pages/{id}/submit-review', [PageController::class, 'submitForReview'])->name('api.v1.pages.submit-review');
        Route::post('/pages/{id}/approve', [PageController::class, 'approve'])->name('api.v1.pages.approve');
        Route::post('/pages/{id}/reject', [PageController::class, 'reject'])->name('api.v1.pages.reject');
        Route::post('/pages/{id}/publish', [PageController::class, 'publish'])->name('api.v1.pages.publish');
        Route::post('/pages/{id}/schedule', [PageController::class, 'schedule'])->name('api.v1.pages.schedule');
        Route::post('/pages/{id}/cancel-schedule', [PageController::class, 'cancelSchedule'])->name('api.v1.pages.cancel-schedule');
        Route::post('/pages/{id}/unpublish', [PageController::class, 'unpublish'])->name('api.v1.pages.unpublish');
        Route::post('/pages/{id}/archive', [PageController::class, 'archive'])->name('api.v1.pages.archive');
        Route::post('/pages/{id}/unarchive', [PageController::class, 'unarchive'])->name('api.v1.pages.unarchive');

        // Pages Hierarchy & Settings
        Route::post('/pages/reorder', [PageController::class, 'reorder'])->name('api.v1.pages.reorder');
        Route::post('/pages/{id}/move', [PageController::class, 'move'])->name('api.v1.pages.move');
        Route::post('/pages/{id}/homepage', [PageController::class, 'setHomepage'])->name('api.v1.pages.homepage');
        Route::post('/pages/{id}/404', [PageController::class, 'set404'])->name('api.v1.pages.404');
        Route::put('/pages/{id}/template', [PageController::class, 'changeTemplate'])->name('api.v1.pages.template');

        // Pages Sections
        Route::post('/pages/{id}/sections', [PageController::class, 'addSection'])->name('api.v1.pages.add-section');
        Route::put('/pages/{id}/sections/{sectionId}', [PageController::class, 'updateSection'])->name('api.v1.pages.update-section');
        Route::delete('/pages/{id}/sections/{sectionId}', [PageController::class, 'deleteSection'])->name('api.v1.pages.delete-section');
        Route::put('/pages/{id}/sections/reorder', [PageController::class, 'reorderSections'])->name('api.v1.pages.reorder-sections');

        // Pages Lock & Clone
        Route::post('/pages/{id}/lock', [PageController::class, 'lock'])->name('api.v1.pages.lock');
        Route::post('/pages/{id}/unlock', [PageController::class, 'unlock'])->name('api.v1.pages.unlock');
        Route::post('/pages/{id}/duplicate', [PageController::class, 'duplicate'])->name('api.v1.pages.duplicate');
        Route::get('/pages/{id}/preview', [PageController::class, 'preview'])->name('api.v1.pages.preview');

        // Pages Revisions
        Route::get('/pages/{id}/revisions', [PageController::class, 'revisions'])->name('api.v1.pages.revisions');
        Route::post('/pages/{id}/revisions/restore', [PageController::class, 'restoreRevision'])->name('api.v1.pages.restore-revision');
    });
});
