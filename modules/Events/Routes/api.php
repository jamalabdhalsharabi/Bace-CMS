<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Http\Controllers\Api\EventController;

/*
|--------------------------------------------------------------------------
| Events Module API Routes
|--------------------------------------------------------------------------
*/

// V1 Routes - Full featured EventController
Route::prefix('api/v1/events')->middleware(['api'])->name('api.events.')->group(function () {
    // Public routes
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/upcoming', [EventController::class, 'upcoming'])->name('upcoming');
    Route::get('/past', [EventController::class, 'past'])->name('past');
    Route::get('/slug/{slug}', [EventController::class, 'showBySlug'])->name('slug');
    Route::get('/{id}', [EventController::class, 'show'])->name('show');
    Route::get('/{id}/stats', [EventController::class, 'stats'])->name('stats');
    Route::post('/{id}/register', [EventController::class, 'register'])->name('register');
    Route::get('/{id}/calendar', [EventController::class, 'addToCalendar'])->name('calendar');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [EventController::class, 'store'])->name('store');
        Route::put('/{id}', [EventController::class, 'update'])->name('update');
        Route::delete('/{id}', [EventController::class, 'destroy'])->name('destroy');

        // Workflow
        Route::post('/{id}/publish', [EventController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [EventController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/schedule', [EventController::class, 'schedule'])->name('schedule');
        Route::post('/{id}/cancel', [EventController::class, 'cancel'])->name('cancel');
        Route::post('/{id}/postpone', [EventController::class, 'postpone'])->name('postpone');
        Route::post('/{id}/feature', [EventController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [EventController::class, 'unfeature'])->name('unfeature');
        Route::post('/{id}/duplicate', [EventController::class, 'duplicate'])->name('duplicate');
        Route::post('/recurring', [EventController::class, 'createRecurring'])->name('create-recurring');

        // Registrations
        Route::get('/{id}/registrations', [EventController::class, 'registrations'])->name('registrations');
        Route::get('/{id}/registrations/export', [EventController::class, 'exportRegistrations'])->name('export-registrations');
        Route::post('/{id}/cancel-registration', [EventController::class, 'cancelRegistration'])->name('cancel-registration');
        Route::post('/{id}/confirm-attendance', [EventController::class, 'confirmAttendance'])->name('confirm-attendance');
        Route::post('/{id}/check-in', [EventController::class, 'checkIn'])->name('check-in');
        Route::post('/{id}/send-reminder', [EventController::class, 'sendReminder'])->name('send-reminder');

        // Speakers
        Route::post('/{id}/speakers', [EventController::class, 'addSpeaker'])->name('add-speaker');
        Route::delete('/{id}/speakers/{speakerId}', [EventController::class, 'removeSpeaker'])->name('remove-speaker');

        // Agenda
        Route::post('/{id}/agenda', [EventController::class, 'addAgendaItem'])->name('add-agenda');
        Route::put('/{id}/agenda/{itemId}', [EventController::class, 'updateAgendaItem'])->name('update-agenda');
        Route::delete('/{id}/agenda/{itemId}', [EventController::class, 'removeAgendaItem'])->name('remove-agenda');

        // Venue & Online
        Route::post('/{id}/venue', [EventController::class, 'setVenue'])->name('set-venue');
        Route::post('/{id}/online-details', [EventController::class, 'setOnlineDetails'])->name('set-online-details');
        Route::post('/{id}/capacity', [EventController::class, 'setCapacity'])->name('set-capacity');
        Route::post('/{id}/enable-waiting-list', [EventController::class, 'enableWaitingList'])->name('enable-waiting-list');
    });
});

