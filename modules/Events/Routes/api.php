<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Http\Controllers\Api\EventDetailsController;
use Modules\Events\Http\Controllers\Api\EventListingController;
use Modules\Events\Http\Controllers\Api\EventManagementController;
use Modules\Events\Http\Controllers\Api\EventRegistrationController;

/*
|--------------------------------------------------------------------------
| Events Module API V1 Routes - Feature-Based Controllers
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1/events')->middleware(['api'])->name('api.v1.events.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Event Listing Routes (Read-Only)
    |--------------------------------------------------------------------------
    */
    Route::get('/', [EventListingController::class, 'index'])->name('index');
    Route::get('/upcoming', [EventListingController::class, 'upcoming'])->name('upcoming');
    Route::get('/past', [EventListingController::class, 'past'])->name('past');
    Route::get('/slug/{slug}', [EventListingController::class, 'showBySlug'])->name('slug');
    Route::get('/{id}', [EventListingController::class, 'show'])->name('show');
    Route::get('/{id}/stats', [EventListingController::class, 'stats'])->name('stats');

    /*
    |--------------------------------------------------------------------------
    | Event Registration Routes (Public)
    |--------------------------------------------------------------------------
    */
    Route::post('/{id}/register', [EventRegistrationController::class, 'register'])->name('register');
    Route::get('/{id}/calendar', [EventRegistrationController::class, 'addToCalendar'])->name('calendar');

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth.api')->group(function () {

        /*
        |--------------------------------------------------------------------------
        | Event Management Routes (CRUD & Workflow)
        |--------------------------------------------------------------------------
        */
        Route::post('/', [EventManagementController::class, 'store'])->name('store');
        Route::put('/{id}', [EventManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [EventManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/publish', [EventManagementController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [EventManagementController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/schedule', [EventManagementController::class, 'schedule'])->name('schedule');
        Route::post('/{id}/cancel', [EventManagementController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/postpone', [EventManagementController::class, 'postpone'])->name('postpone');
        Route::post('/{id}/feature', [EventManagementController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [EventManagementController::class, 'unfeature'])->name('unfeature');
        Route::post('/{id}/duplicate', [EventManagementController::class, 'duplicate'])->name('duplicate');
        Route::post('/recurring', [EventManagementController::class, 'createRecurring'])->name('create-recurring');

        /*
        |--------------------------------------------------------------------------
        | Event Registration Routes (Protected)
        |--------------------------------------------------------------------------
        */
        Route::get('/{id}/registrations', [EventRegistrationController::class, 'registrations'])->name('registrations');
        Route::get('/{id}/registrations/export', [EventRegistrationController::class, 'exportRegistrations'])->name('export-registrations');
        Route::post('/{id}/cancel-registration', [EventRegistrationController::class, 'cancelRegistration'])->name('cancel-registration');
        Route::post('/{id}/confirm-attendance', [EventRegistrationController::class, 'confirmAttendance'])->name('confirm-attendance');
        Route::post('/{id}/check-in', [EventRegistrationController::class, 'checkIn'])->name('check-in');
        Route::post('/{id}/send-reminder', [EventRegistrationController::class, 'sendReminder'])->name('send-reminder');

        /*
        |--------------------------------------------------------------------------
        | Event Details Routes (Speakers, Agenda, Venue)
        |--------------------------------------------------------------------------
        */
        Route::post('/{id}/speakers', [EventDetailsController::class, 'addSpeaker'])->name('add-speaker');
        Route::delete('/{id}/speakers/{speakerId}', [EventDetailsController::class, 'removeSpeaker'])->name('remove-speaker');
        Route::post('/{id}/agenda', [EventDetailsController::class, 'addAgendaItem'])->name('add-agenda');
        Route::put('/{id}/agenda/{itemId}', [EventDetailsController::class, 'updateAgendaItem'])->name('update-agenda');
        Route::delete('/{id}/agenda/{itemId}', [EventDetailsController::class, 'removeAgendaItem'])->name('remove-agenda');
        Route::post('/{id}/venue', [EventDetailsController::class, 'setVenue'])->name('set-venue');
        Route::post('/{id}/online-details', [EventDetailsController::class, 'setOnlineDetails'])->name('set-online-details');
        Route::post('/{id}/capacity', [EventDetailsController::class, 'setCapacity'])->name('set-capacity');
        Route::post('/{id}/enable-waiting-list', [EventDetailsController::class, 'enableWaitingList'])->name('enable-waiting-list');
    });
});
