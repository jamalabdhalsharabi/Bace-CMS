<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\Api\ProjectController;

Route::prefix('api/v1/projects')->middleware(['api'])->name('api.projects.')->group(function () {
    // Public routes
    Route::get('/', [ProjectController::class, 'index'])->name('index');
    Route::get('/featured', [ProjectController::class, 'featured'])->name('featured');
    Route::get('/slug/{slug}', [ProjectController::class, 'showBySlug'])->name('slug');
    Route::get('/{id}', [ProjectController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::put('/{id}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [ProjectController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/restore', [ProjectController::class, 'restore'])->name('restore');

        // Workflow
        Route::post('/{id}/publish', [ProjectController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [ProjectController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/schedule', [ProjectController::class, 'schedule'])->name('schedule');
        Route::post('/{id}/cancel-schedule', [ProjectController::class, 'cancelSchedule'])->name('cancel-schedule');
        Route::post('/{id}/save-draft', [ProjectController::class, 'saveDraft'])->name('save-draft');
        Route::post('/{id}/submit-review', [ProjectController::class, 'submitForReview'])->name('submit-review');
        Route::post('/{id}/start-review', [ProjectController::class, 'startReview'])->name('start-review');
        Route::post('/{id}/approve', [ProjectController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [ProjectController::class, 'reject'])->name('reject');
        Route::post('/{id}/archive', [ProjectController::class, 'archive'])->name('archive');
        Route::post('/{id}/unarchive', [ProjectController::class, 'unarchive'])->name('unarchive');

        // Features
        Route::post('/{id}/feature', [ProjectController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [ProjectController::class, 'unfeature'])->name('unfeature');
        Route::post('/{id}/duplicate', [ProjectController::class, 'duplicate'])->name('duplicate');

        // Translations
        Route::post('/{id}/translations', [ProjectController::class, 'createTranslation'])->name('create-translation');

        // Gallery
        Route::post('/{id}/gallery', [ProjectController::class, 'addGalleryImage'])->name('add-gallery-image');
        Route::delete('/{id}/gallery/{mediaId}', [ProjectController::class, 'removeGalleryImage'])->name('remove-gallery-image');
        Route::post('/{id}/gallery/reorder', [ProjectController::class, 'reorderGallery'])->name('reorder-gallery');

        // Comparison & Case Study
        Route::post('/{id}/comparison', [ProjectController::class, 'createComparison'])->name('create-comparison');
        Route::post('/{id}/case-study', [ProjectController::class, 'addCaseStudy'])->name('add-case-study');
        Route::put('/{id}/case-study', [ProjectController::class, 'updateCaseStudy'])->name('update-case-study');
        Route::post('/{id}/metrics', [ProjectController::class, 'addMetrics'])->name('add-metrics');

        // Relationships
        Route::post('/{id}/technologies', [ProjectController::class, 'linkTechnologies'])->name('link-technologies');
        Route::post('/{id}/industries', [ProjectController::class, 'linkIndustries'])->name('link-industries');
        Route::post('/{id}/testimonial', [ProjectController::class, 'linkTestimonial'])->name('link-testimonial');
        Route::post('/{id}/request-testimonial', [ProjectController::class, 'requestTestimonial'])->name('request-testimonial');
        Route::post('/{id}/related', [ProjectController::class, 'linkRelated'])->name('link-related');

        // Export & Revisions
        Route::get('/{id}/export-pdf', [ProjectController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{id}/revisions', [ProjectController::class, 'revisions'])->name('revisions');
        Route::post('/{id}/revisions/{revisionId}/restore', [ProjectController::class, 'restoreRevision'])->name('restore-revision');
    });
});
