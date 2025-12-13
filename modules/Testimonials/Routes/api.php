<?php

use Illuminate\Support\Facades\Route;
use Modules\Testimonials\Http\Controllers\Api\TestimonialController;

Route::prefix('api/v1/testimonials')->middleware(['api'])->name('api.testimonials.')->group(function () {
    // Public routes
    Route::get('/', [TestimonialController::class, 'index'])->name('index');
    Route::get('/featured', [TestimonialController::class, 'featured'])->name('featured');
    Route::get('/rating-stats', [TestimonialController::class, 'updateRatingStats'])->name('rating-stats');
    Route::get('/{id}', [TestimonialController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [TestimonialController::class, 'store'])->name('store');
        Route::put('/{id}', [TestimonialController::class, 'update'])->name('update');
        Route::delete('/{id}', [TestimonialController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [TestimonialController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/restore', [TestimonialController::class, 'restore'])->name('restore');

        // Workflow
        Route::post('/{id}/submit-review', [TestimonialController::class, 'submitForReview'])->name('submit-review');
        Route::post('/{id}/start-review', [TestimonialController::class, 'startReview'])->name('start-review');
        Route::post('/{id}/approve', [TestimonialController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [TestimonialController::class, 'reject'])->name('reject');
        Route::post('/{id}/publish', [TestimonialController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [TestimonialController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [TestimonialController::class, 'archive'])->name('archive');

        // Features
        Route::post('/{id}/feature', [TestimonialController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [TestimonialController::class, 'unfeature'])->name('unfeature');
        Route::post('/{id}/verify', [TestimonialController::class, 'verifyClient'])->name('verify');

        // Request & Import
        Route::post('/request', [TestimonialController::class, 'requestTestimonial'])->name('request');
        Route::post('/import', [TestimonialController::class, 'import'])->name('import');

        // Entity Linking
        Route::post('/{id}/link-entity', [TestimonialController::class, 'linkEntity'])->name('link-entity');
        Route::post('/{id}/unlink-entity', [TestimonialController::class, 'unlinkEntity'])->name('unlink-entity');

        // Reorder
        Route::post('/reorder', [TestimonialController::class, 'reorder'])->name('reorder');
    });
});
