<?php

use Illuminate\Support\Facades\Route;
use Modules\Services\Http\Controllers\Api\ServiceController;

Route::prefix('api/v1')->middleware(['api'])->group(function () {
    // Public
    Route::get('/services', [ServiceController::class, 'index'])->name('api.v1.services.index');
    Route::get('/services/slug/{slug}', [ServiceController::class, 'showBySlug'])->name('api.v1.services.slug');
    Route::get('/services/{id}', [ServiceController::class, 'show'])->name('api.v1.services.show');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/admin/services', [ServiceController::class, 'store'])->name('api.v1.services.store');
        Route::put('/admin/services/{id}', [ServiceController::class, 'update'])->name('api.v1.services.update');
        Route::delete('/admin/services/{id}', [ServiceController::class, 'destroy'])->name('api.v1.services.destroy');
        Route::delete('/admin/services/{id}/force', [ServiceController::class, 'forceDestroy'])->name('api.v1.services.force-destroy');
        Route::post('/admin/services/{id}/restore', [ServiceController::class, 'restore'])->name('api.v1.services.restore');

        // Workflow
        Route::post('/admin/services/{id}/draft', [ServiceController::class, 'saveDraft'])->name('api.v1.services.draft');
        Route::post('/admin/services/{id}/submit-review', [ServiceController::class, 'submitForReview'])->name('api.v1.services.submit-review');
        Route::post('/admin/services/{id}/start-review', [ServiceController::class, 'startReview'])->name('api.v1.services.start-review');
        Route::post('/admin/services/{id}/approve', [ServiceController::class, 'approve'])->name('api.v1.services.approve');
        Route::post('/admin/services/{id}/reject', [ServiceController::class, 'reject'])->name('api.v1.services.reject');
        Route::post('/admin/services/{id}/publish', [ServiceController::class, 'publish'])->name('api.v1.services.publish');
        Route::post('/admin/services/{id}/schedule', [ServiceController::class, 'schedule'])->name('api.v1.services.schedule');
        Route::post('/admin/services/{id}/cancel-schedule', [ServiceController::class, 'cancelSchedule'])->name('api.v1.services.cancel-schedule');
        Route::post('/admin/services/{id}/unpublish', [ServiceController::class, 'unpublish'])->name('api.v1.services.unpublish');
        Route::post('/admin/services/{id}/archive', [ServiceController::class, 'archive'])->name('api.v1.services.archive');
        Route::post('/admin/services/{id}/unarchive', [ServiceController::class, 'unarchive'])->name('api.v1.services.unarchive');

        // Features
        Route::post('/admin/services/{id}/feature', [ServiceController::class, 'feature'])->name('api.v1.services.feature');
        Route::post('/admin/services/{id}/unfeature', [ServiceController::class, 'unfeature'])->name('api.v1.services.unfeature');
        Route::post('/admin/services/{id}/clone', [ServiceController::class, 'clone'])->name('api.v1.services.clone');
        Route::put('/admin/services/reorder', [ServiceController::class, 'reorder'])->name('api.v1.services.reorder');

        // Translations
        Route::post('/admin/services/{id}/translations', [ServiceController::class, 'createTranslation'])->name('api.v1.services.translations');

        // Media
        Route::post('/admin/services/{id}/media', [ServiceController::class, 'attachMedia'])->name('api.v1.services.attach-media');
        Route::delete('/admin/services/{id}/media', [ServiceController::class, 'detachMedia'])->name('api.v1.services.detach-media');
        Route::put('/admin/services/{id}/media/reorder', [ServiceController::class, 'reorderMedia'])->name('api.v1.services.reorder-media');

        // Categories & Related
        Route::put('/admin/services/{id}/categories', [ServiceController::class, 'syncCategories'])->name('api.v1.services.categories');
        Route::put('/admin/services/{id}/related', [ServiceController::class, 'attachRelated'])->name('api.v1.services.related');

        // Revisions
        Route::get('/admin/services/{id}/revisions', [ServiceController::class, 'revisions'])->name('api.v1.services.revisions');
        Route::post('/admin/services/{id}/revisions/compare', [ServiceController::class, 'compareRevisions'])->name('api.v1.services.compare-revisions');
        Route::post('/admin/services/{id}/revisions/restore', [ServiceController::class, 'restoreRevision'])->name('api.v1.services.restore-revision');

        // Search Index
        Route::post('/admin/services/{id}/index', [ServiceController::class, 'indexInSearch'])->name('api.v1.services.index-search');
        Route::delete('/admin/services/{id}/index', [ServiceController::class, 'removeFromIndex'])->name('api.v1.services.remove-index');
    });
});
