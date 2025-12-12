<?php

use Illuminate\Support\Facades\Route;
use Modules\Events\Http\Controllers\Api\EventController;

Route::prefix('api/v1/events')->middleware(['api'])->name('api.v1.events.')->group(function () {
    Route::get('/', [EventController::class, 'index'])->name('index');
    Route::get('/slug/{slug}', [EventController::class, 'showBySlug'])->name('slug');
    Route::post('/{id}/register', [EventController::class, 'register'])->name('register');
    Route::post('/{id}/waitlist', [EventController::class, 'joinWaitlist'])->name('waitlist');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [EventController::class, 'store'])->name('store');
        Route::get('/{id}', [EventController::class, 'show'])->name('show');
        Route::put('/{id}', [EventController::class, 'update'])->name('update');
        Route::delete('/{id}', [EventController::class, 'destroy'])->name('destroy');

        // Workflow
        Route::post('/{id}/submit-review', [EventController::class, 'submitForReview'])->name('submit-review');
        Route::post('/{id}/approve', [EventController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [EventController::class, 'reject'])->name('reject');
        Route::post('/{id}/publish', [EventController::class, 'publish'])->name('publish');
        Route::post('/{id}/schedule', [EventController::class, 'schedule'])->name('schedule');
        Route::post('/{id}/unpublish', [EventController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [EventController::class, 'archive'])->name('archive');

        // Registration Management
        Route::post('/{id}/registration/open', [EventController::class, 'openRegistration'])->name('open-registration');
        Route::post('/{id}/registration/close', [EventController::class, 'closeRegistration'])->name('close-registration');
        Route::post('/registrations/{regId}/confirm-payment', [EventController::class, 'confirmPayment'])->name('confirm-payment');
        Route::post('/registrations/{regId}/cancel', [EventController::class, 'cancelRegistration'])->name('cancel-registration');
        Route::post('/registrations/{regId}/refund', [EventController::class, 'refundRegistration'])->name('refund');
        Route::post('/registrations/{regId}/promote', [EventController::class, 'promoteFromWaitlist'])->name('promote-waitlist');
        Route::post('/registrations/{regId}/check-in', [EventController::class, 'checkIn'])->name('check-in');

        // Ticket Types
        Route::post('/{id}/ticket-types', [EventController::class, 'addTicketType'])->name('add-ticket-type');
        Route::put('/{id}/ticket-types/{ticketId}', [EventController::class, 'updateTicketType'])->name('update-ticket-type');
        Route::delete('/{id}/ticket-types/{ticketId}', [EventController::class, 'deleteTicketType'])->name('delete-ticket-type');
        Route::post('/{id}/ticket-types/{ticketId}/toggle', [EventController::class, 'toggleTicketType'])->name('toggle-ticket-type');

        // Sessions
        Route::post('/{id}/sessions', [EventController::class, 'addSession'])->name('add-session');
        Route::put('/{id}/sessions/{sessionId}', [EventController::class, 'updateSession'])->name('update-session');
        Route::delete('/{id}/sessions/{sessionId}', [EventController::class, 'deleteSession'])->name('delete-session');
        Route::post('/{id}/sessions/{sessionId}/cancel', [EventController::class, 'cancelSession'])->name('cancel-session');

        // Speakers
        Route::post('/{id}/speakers', [EventController::class, 'addSpeaker'])->name('add-speaker');
        Route::delete('/{id}/speakers/{speakerId}', [EventController::class, 'removeSpeaker'])->name('remove-speaker');
        Route::post('/{id}/speakers/{speakerId}/invite', [EventController::class, 'sendSpeakerInvite'])->name('speaker-invite');

        // Event Lifecycle
        Route::post('/{id}/start', [EventController::class, 'startEvent'])->name('start');
        Route::post('/{id}/end', [EventController::class, 'endEvent'])->name('end');
        Route::post('/{id}/certificates', [EventController::class, 'sendCertificates'])->name('certificates');
        Route::post('/{id}/recordings', [EventController::class, 'publishRecordings'])->name('recordings');

        // Other
        Route::post('/{id}/postpone', [EventController::class, 'postponeEvent'])->name('postpone');
        Route::post('/{id}/cancel', [EventController::class, 'cancelEvent'])->name('cancel');
        Route::post('/{id}/duplicate', [EventController::class, 'duplicate'])->name('duplicate');
    });
});
