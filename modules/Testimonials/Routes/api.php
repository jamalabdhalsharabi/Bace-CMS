<?php

use Illuminate\Support\Facades\Route;
use Modules\Testimonials\Http\Controllers\Api\TestimonialController;

Route::prefix('api/v1/testimonials')->middleware(['api'])->name('api.v1.testimonials.')->group(function () {
    Route::get('/', [TestimonialController::class, 'index'])->name('index');
    Route::post('/submit', [TestimonialController::class, 'submit'])->name('submit');
    
    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [TestimonialController::class, 'store'])->name('store');
        Route::get('/{id}', [TestimonialController::class, 'show'])->name('show');
        Route::put('/{id}', [TestimonialController::class, 'update'])->name('update');
        Route::delete('/{id}', [TestimonialController::class, 'destroy'])->name('destroy');

        // Workflow
        Route::post('/{id}/approve', [TestimonialController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [TestimonialController::class, 'reject'])->name('reject');
        Route::post('/{id}/publish', [TestimonialController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [TestimonialController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [TestimonialController::class, 'archive'])->name('archive');

        // Verification
        Route::post('/{id}/verify', [TestimonialController::class, 'verify'])->name('verify');
        Route::post('/{id}/send-verification', [TestimonialController::class, 'sendVerification'])->name('send-verification');

        // Features
        Route::post('/{id}/feature', [TestimonialController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [TestimonialController::class, 'unfeature'])->name('unfeature');

        // Relations
        Route::put('/{id}/link', [TestimonialController::class, 'linkToEntity'])->name('link');
        Route::delete('/{id}/link', [TestimonialController::class, 'unlinkFromEntity'])->name('unlink');

        // Import
        Route::post('/import', [TestimonialController::class, 'import'])->name('import');
        Route::post('/request', [TestimonialController::class, 'requestTestimonial'])->name('request');

        // Missing: Reorder, Update Rating, Restore, Force Delete
        Route::post('/reorder', [TestimonialController::class, 'reorder'])->name('reorder');
        Route::post('/{id}/rating', [TestimonialController::class, 'updateRating'])->name('update-rating');
        Route::post('/{id}/restore', [TestimonialController::class, 'restore'])->name('restore');
        Route::delete('/{id}/force', [TestimonialController::class, 'forceDestroy'])->name('force-destroy');
    });
});
