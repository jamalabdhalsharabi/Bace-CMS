<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Http\Controllers\Api\EventControllerV2;

/*
|--------------------------------------------------------------------------
| Events Module API V2 Routes
|--------------------------------------------------------------------------
|
| Clean Architecture routes using specialized services.
|
*/

Route::prefix('api/v2/events')->middleware(['api'])->name('api.v2.events.')->group(function () {
    // Public routes
    Route::get('/', [EventControllerV2::class, 'index'])->name('index');
    Route::get('/slug/{slug}', [EventControllerV2::class, 'showBySlug'])->name('slug');
    Route::get('/upcoming', [EventControllerV2::class, 'upcoming'])->name('upcoming');
    Route::get('/ongoing', [EventControllerV2::class, 'ongoing'])->name('ongoing');
    Route::get('/past', [EventControllerV2::class, 'past'])->name('past');

    // Public registration
    Route::post('/{id}/register', [EventControllerV2::class, 'register'])->name('register');
    Route::get('/{id}/stats', [EventControllerV2::class, 'registrationStats'])->name('stats');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [EventControllerV2::class, 'store'])->name('store');
        Route::get('/{id}', [EventControllerV2::class, 'show'])->name('show');
        Route::put('/{id}', [EventControllerV2::class, 'update'])->name('update');
        Route::delete('/{id}', [EventControllerV2::class, 'destroy'])->name('destroy');

        // Workflow
        Route::post('/{id}/publish', [EventControllerV2::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [EventControllerV2::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/cancel', [EventControllerV2::class, 'cancel'])->name('cancel');
        Route::post('/{id}/postpone', [EventControllerV2::class, 'postpone'])->name('postpone');
        Route::post('/{id}/duplicate', [EventControllerV2::class, 'duplicate'])->name('duplicate');

        // Registrations
        Route::get('/{id}/registrations', [EventControllerV2::class, 'registrations'])->name('registrations');
        Route::post('/{id}/registrations/{registrationId}/cancel', [EventControllerV2::class, 'cancelRegistration'])->name('cancel-registration');
        Route::post('/{id}/registrations/{registrationId}/check-in', [EventControllerV2::class, 'checkIn'])->name('check-in');
    });
});
