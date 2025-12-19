<?php

use Illuminate\Support\Facades\Route;
use Modules\Testimonials\Http\Controllers\Api\TestimonialListingController;
use Modules\Testimonials\Http\Controllers\Api\TestimonialManagementController;

/*
|--------------------------------------------------------------------------
| Testimonials Module API V1 Routes - Feature-Based Controllers
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1/testimonials')->middleware(['api'])->name('api.v1.testimonials.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Testimonial Listing Routes (Read-Only)
    |--------------------------------------------------------------------------
    */
    Route::get('/', [TestimonialListingController::class, 'index'])->name('index');
    Route::get('/featured', [TestimonialListingController::class, 'featured'])->name('featured');
    Route::get('/rating-stats', [TestimonialListingController::class, 'ratingStats'])->name('rating-stats');
    Route::get('/{id}', [TestimonialListingController::class, 'show'])->name('show');

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [TestimonialManagementController::class, 'store'])->name('store');
        Route::put('/{id}', [TestimonialManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [TestimonialManagementController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [TestimonialManagementController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/restore', [TestimonialManagementController::class, 'restore'])->name('restore');

        // Workflow
        Route::post('/{id}/submit-review', [TestimonialManagementController::class, 'submitForReview'])->name('submit-review');
        Route::post('/{id}/start-review', [TestimonialManagementController::class, 'startReview'])->name('start-review');
        Route::post('/{id}/approve', [TestimonialManagementController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [TestimonialManagementController::class, 'reject'])->name('reject');
        Route::post('/{id}/publish', [TestimonialManagementController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [TestimonialManagementController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [TestimonialManagementController::class, 'archive'])->name('archive');

        // Features
        Route::post('/{id}/feature', [TestimonialManagementController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [TestimonialManagementController::class, 'unfeature'])->name('unfeature');
        Route::post('/{id}/verify', [TestimonialManagementController::class, 'verifyClient'])->name('verify');

        // Request & Import
        Route::post('/request', [TestimonialManagementController::class, 'requestTestimonial'])->name('request');
        Route::post('/import', [TestimonialManagementController::class, 'import'])->name('import');

        // Entity Linking
        Route::post('/{id}/link-entity', [TestimonialManagementController::class, 'linkEntity'])->name('link-entity');
        Route::post('/{id}/unlink-entity', [TestimonialManagementController::class, 'unlinkEntity'])->name('unlink-entity');

        // Reorder
        Route::post('/reorder', [TestimonialManagementController::class, 'reorder'])->name('reorder');
    });
});
