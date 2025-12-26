<?php

use Illuminate\Support\Facades\Route;
use Modules\Projects\Http\Controllers\Api\ProjectDetailsController;
use Modules\Projects\Http\Controllers\Api\ProjectListingController;
use Modules\Projects\Http\Controllers\Api\ProjectManagementController;

/*
|--------------------------------------------------------------------------
| Projects Module API V1 Routes - Feature-Based Controllers
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1/projects')->middleware(['api'])->name('api.v1.projects.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Project Listing Routes (Read-Only)
    |--------------------------------------------------------------------------
    */
    Route::get('/', [ProjectListingController::class, 'index'])->name('index');
    Route::get('/featured', [ProjectListingController::class, 'featured'])->name('featured');
    Route::get('/slug/{slug}', [ProjectListingController::class, 'showBySlug'])->name('slug');
    Route::get('/{id}', [ProjectListingController::class, 'show'])->name('show');

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth.api')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Project Management Routes (CRUD & Workflow)
        |--------------------------------------------------------------------------
        */
        Route::post('/', [ProjectManagementController::class, 'store'])->name('store');
        Route::put('/{id}', [ProjectManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProjectManagementController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [ProjectManagementController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/restore', [ProjectManagementController::class, 'restore'])->name('restore');
        Route::post('/{id}/publish', [ProjectManagementController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [ProjectManagementController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/schedule', [ProjectManagementController::class, 'schedule'])->name('schedule');
        Route::post('/{id}/cancel-schedule', [ProjectManagementController::class, 'cancelSchedule'])->name('cancel-schedule');
        Route::post('/{id}/save-draft', [ProjectManagementController::class, 'saveDraft'])->name('save-draft');
        Route::post('/{id}/submit-review', [ProjectManagementController::class, 'submitForReview'])->name('submit-review');
        Route::post('/{id}/start-review', [ProjectManagementController::class, 'startReview'])->name('start-review');
        Route::post('/{id}/approve', [ProjectManagementController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [ProjectManagementController::class, 'reject'])->name('reject');
        Route::post('/{id}/archive', [ProjectManagementController::class, 'archive'])->name('archive');
        Route::post('/{id}/unarchive', [ProjectManagementController::class, 'unarchive'])->name('unarchive');
        Route::post('/{id}/feature', [ProjectManagementController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [ProjectManagementController::class, 'unfeature'])->name('unfeature');
        Route::post('/{id}/duplicate', [ProjectManagementController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/translations', [ProjectManagementController::class, 'createTranslation'])->name('create-translation');
        Route::get('/{id}/export-pdf', [ProjectManagementController::class, 'exportPdf'])->name('export-pdf');
        Route::get('/{id}/revisions', [ProjectListingController::class, 'revisions'])->name('revisions');
        Route::post('/{id}/revisions/{revisionId}/restore', [ProjectManagementController::class, 'restoreRevision'])->name('restore-revision');

        /*
        |--------------------------------------------------------------------------
        | Project Details Routes (Gallery, Case Study, Relationships)
        |--------------------------------------------------------------------------
        */
        Route::post('/{id}/gallery', [ProjectDetailsController::class, 'addGalleryImage'])->name('add-gallery-image');
        Route::delete('/{id}/gallery/{mediaId}', [ProjectDetailsController::class, 'removeGalleryImage'])->name('remove-gallery-image');
        Route::post('/{id}/gallery/reorder', [ProjectDetailsController::class, 'reorderGallery'])->name('reorder-gallery');
        Route::post('/{id}/comparison', [ProjectDetailsController::class, 'createComparison'])->name('create-comparison');
        Route::post('/{id}/case-study', [ProjectDetailsController::class, 'addCaseStudy'])->name('add-case-study');
        Route::put('/{id}/case-study', [ProjectDetailsController::class, 'updateCaseStudy'])->name('update-case-study');
        Route::post('/{id}/metrics', [ProjectDetailsController::class, 'addMetrics'])->name('add-metrics');
        Route::post('/{id}/technologies', [ProjectDetailsController::class, 'linkTechnologies'])->name('link-technologies');
        Route::post('/{id}/industries', [ProjectDetailsController::class, 'linkIndustries'])->name('link-industries');
        Route::post('/{id}/testimonial', [ProjectDetailsController::class, 'linkTestimonial'])->name('link-testimonial');
        Route::post('/{id}/request-testimonial', [ProjectDetailsController::class, 'requestTestimonial'])->name('request-testimonial');
        Route::post('/{id}/related', [ProjectDetailsController::class, 'linkRelated'])->name('link-related');
    });
});
