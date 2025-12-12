<?php

use Illuminate\Support\Facades\Route;
use Modules\Forms\Http\Controllers\Api\FormController;

Route::prefix('api/v1')->middleware(['api'])->group(function () {
    Route::get('/forms/slug/{slug}', [FormController::class, 'showBySlug'])->name('api.v1.forms.slug');
    Route::post('/forms/{slug}/submit', [FormController::class, 'submit'])->name('api.v1.forms.submit');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::get('/forms', [FormController::class, 'index'])->name('api.v1.forms.index');
        Route::post('/forms', [FormController::class, 'store'])->name('api.v1.forms.store');
        Route::get('/forms/{id}', [FormController::class, 'show'])->name('api.v1.forms.show');
        Route::put('/forms/{id}', [FormController::class, 'update'])->name('api.v1.forms.update');
        Route::delete('/forms/{id}', [FormController::class, 'destroy'])->name('api.v1.forms.destroy');

        // Workflow
        Route::post('/forms/{id}/publish', [FormController::class, 'publish'])->name('api.v1.forms.publish');
        Route::post('/forms/{id}/unpublish', [FormController::class, 'unpublish'])->name('api.v1.forms.unpublish');
        Route::post('/forms/{id}/duplicate', [FormController::class, 'duplicate'])->name('api.v1.forms.duplicate');

        // Fields
        Route::post('/forms/{id}/fields', [FormController::class, 'addField'])->name('api.v1.forms.add-field');
        Route::put('/forms/{id}/fields/{fieldId}', [FormController::class, 'updateField'])->name('api.v1.forms.update-field');
        Route::delete('/forms/{id}/fields/{fieldId}', [FormController::class, 'deleteField'])->name('api.v1.forms.delete-field');
        Route::post('/forms/{id}/fields/reorder', [FormController::class, 'reorderFields'])->name('api.v1.forms.reorder-fields');

        // Submissions
        Route::get('/forms/{id}/submissions', [FormController::class, 'submissions'])->name('api.v1.forms.submissions');
        Route::get('/submissions/{id}', [FormController::class, 'showSubmission'])->name('api.v1.submissions.show');
        Route::patch('/submissions/{id}/status', [FormController::class, 'updateSubmissionStatus'])->name('api.v1.submissions.status');
        Route::post('/submissions/{id}/assign', [FormController::class, 'assignSubmission'])->name('api.v1.submissions.assign');
        Route::post('/submissions/{id}/reply', [FormController::class, 'replyToSubmission'])->name('api.v1.submissions.reply');
        Route::delete('/submissions/{id}', [FormController::class, 'deleteSubmission'])->name('api.v1.submissions.destroy');

        // Spam
        Route::post('/submissions/{id}/spam', [FormController::class, 'markAsSpam'])->name('api.v1.submissions.spam');
        Route::post('/submissions/{id}/not-spam', [FormController::class, 'markAsNotSpam'])->name('api.v1.submissions.not-spam');

        // Bulk
        Route::post('/submissions/bulk/delete', [FormController::class, 'bulkDeleteSubmissions'])->name('api.v1.submissions.bulk-delete');
        Route::post('/submissions/bulk/status', [FormController::class, 'bulkUpdateStatus'])->name('api.v1.submissions.bulk-status');

        // Export & Analytics
        Route::get('/forms/{id}/submissions/export', [FormController::class, 'exportSubmissions'])->name('api.v1.forms.export');
        Route::get('/forms/{id}/analytics', [FormController::class, 'analytics'])->name('api.v1.forms.analytics');

        // CRM Sync
        Route::post('/submissions/{id}/sync-crm', [FormController::class, 'syncToCrm'])->name('api.v1.submissions.sync-crm');
    });
});
