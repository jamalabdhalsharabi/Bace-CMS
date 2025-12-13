<?php

use Illuminate\Support\Facades\Route;
use Modules\Testimonials\Http\Controllers\Api\TestimonialController;

Route::prefix('api/v1/testimonials')->middleware(['api'])->name('api.testimonials.')->group(function () {
    Route::get('/', [TestimonialController::class, 'index'])->name('index');
    Route::get('/featured', [TestimonialController::class, 'featured'])->name('featured');
    Route::get('/{id}', [TestimonialController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [TestimonialController::class, 'store'])->name('store');
        Route::put('/{id}', [TestimonialController::class, 'update'])->name('update');
        Route::delete('/{id}', [TestimonialController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/approve', [TestimonialController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [TestimonialController::class, 'reject'])->name('reject');
        Route::post('/{id}/feature', [TestimonialController::class, 'feature'])->name('feature');
    });
});
