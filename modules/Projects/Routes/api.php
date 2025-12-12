<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\Api\ProjectController;

Route::prefix('api/v1/projects')->middleware(['api'])->name('api.v1.projects.')->group(function () {
    Route::get('/', [ProjectController::class, 'index'])->name('index');
    Route::get('/slug/{slug}', [ProjectController::class, 'showBySlug'])->name('slug');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [ProjectController::class, 'store'])->name('store');
        Route::get('/{id}', [ProjectController::class, 'show'])->name('show');
        Route::put('/{id}', [ProjectController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProjectController::class, 'destroy'])->name('destroy');

        // Workflow
        Route::post('/{id}/submit-review', [ProjectController::class, 'submitForReview'])->name('submit-review');
        Route::post('/{id}/approve', [ProjectController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [ProjectController::class, 'reject'])->name('reject');
        Route::post('/{id}/publish', [ProjectController::class, 'publish'])->name('publish');
        Route::post('/{id}/schedule', [ProjectController::class, 'schedule'])->name('schedule');
        Route::post('/{id}/unpublish', [ProjectController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [ProjectController::class, 'archive'])->name('archive');

        // Gallery
        Route::post('/{id}/gallery', [ProjectController::class, 'addGalleryImages'])->name('add-gallery');
        Route::delete('/{id}/gallery/{mediaId}', [ProjectController::class, 'removeGalleryImage'])->name('remove-gallery');
        Route::put('/{id}/gallery/reorder', [ProjectController::class, 'reorderGallery'])->name('reorder-gallery');
        Route::put('/{id}/featured-image', [ProjectController::class, 'setFeaturedImage'])->name('featured-image');

        // Case Study & Before/After
        Route::post('/{id}/case-study', [ProjectController::class, 'addCaseStudy'])->name('case-study');
        Route::post('/{id}/before-after', [ProjectController::class, 'addBeforeAfter'])->name('before-after');
        Route::delete('/{id}/before-after/{baId}', [ProjectController::class, 'removeBeforeAfter'])->name('remove-before-after');

        // Other
        Route::post('/{id}/feature', [ProjectController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [ProjectController::class, 'unfeature'])->name('unfeature');
        Route::post('/{id}/duplicate', [ProjectController::class, 'duplicate'])->name('duplicate');
        Route::put('/{id}/categories', [ProjectController::class, 'syncCategories'])->name('categories');
        Route::put('/{id}/related', [ProjectController::class, 'attachRelated'])->name('related');

        // Missing: Translations, Technologies, Industries, PDF Export
        Route::post('/{id}/translations', [ProjectController::class, 'addTranslation'])->name('add-translation');
        Route::put('/{id}/technologies', [ProjectController::class, 'syncTechnologies'])->name('technologies');
        Route::put('/{id}/industries', [ProjectController::class, 'syncIndustries'])->name('industries');
        Route::post('/{id}/testimonial/request', [ProjectController::class, 'requestTestimonial'])->name('request-testimonial');
        Route::get('/{id}/export-pdf', [ProjectController::class, 'exportPdf'])->name('export-pdf');
    });
});
