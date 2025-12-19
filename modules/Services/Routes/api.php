<?php

use Illuminate\Support\Facades\Route;
use Modules\Services\Http\Controllers\Api\ServiceListingController;
use Modules\Services\Http\Controllers\Api\ServiceManagementController;

/*
|--------------------------------------------------------------------------
| Services Module API V1 Routes - Feature-Based Controllers
|--------------------------------------------------------------------------
*/

Route::prefix('api/v1/services')->middleware(['api'])->name('api.v1.services.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Service Listing Routes (Read-Only)
    |--------------------------------------------------------------------------
    */
    Route::get('/', [ServiceListingController::class, 'index'])->name('index');
    Route::get('/slug/{slug}', [ServiceListingController::class, 'showBySlug'])->name('slug');
    Route::get('/{id}', [ServiceListingController::class, 'show'])->name('show');

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [ServiceManagementController::class, 'store'])->name('store');
        Route::put('/{id}', [ServiceManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [ServiceManagementController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [ServiceManagementController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/restore', [ServiceManagementController::class, 'restore'])->name('restore');

        // Workflow
        Route::post('/{id}/publish', [ServiceManagementController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [ServiceManagementController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [ServiceManagementController::class, 'archive'])->name('archive');
        Route::post('/{id}/unarchive', [ServiceManagementController::class, 'unarchive'])->name('unarchive');
        Route::post('/{id}/schedule', [ServiceManagementController::class, 'schedule'])->name('schedule');
        Route::post('/{id}/cancel-schedule', [ServiceManagementController::class, 'cancelSchedule'])->name('cancel-schedule');
        Route::post('/{id}/save-draft', [ServiceManagementController::class, 'saveDraft'])->name('save-draft');
        Route::post('/{id}/submit-review', [ServiceManagementController::class, 'submitForReview'])->name('submit-review');
        Route::post('/{id}/start-review', [ServiceManagementController::class, 'startReview'])->name('start-review');
        Route::post('/{id}/approve', [ServiceManagementController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [ServiceManagementController::class, 'reject'])->name('reject');

        // Features
        Route::post('/{id}/feature', [ServiceManagementController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [ServiceManagementController::class, 'unfeature'])->name('unfeature');
        Route::post('/{id}/clone', [ServiceManagementController::class, 'clone'])->name('clone');
        Route::post('/reorder', [ServiceManagementController::class, 'reorder'])->name('reorder');

        // Translations & Media
        Route::post('/{id}/translations', [ServiceManagementController::class, 'createTranslation'])->name('create-translation');
        Route::post('/{id}/media', [ServiceManagementController::class, 'attachMedia'])->name('attach-media');
        Route::delete('/{id}/media', [ServiceManagementController::class, 'detachMedia'])->name('detach-media');
        Route::post('/{id}/media/reorder', [ServiceManagementController::class, 'reorderMedia'])->name('reorder-media');

        // Categories & Related
        Route::put('/{id}/categories', [ServiceManagementController::class, 'syncCategories'])->name('sync-categories');
        Route::post('/{id}/related', [ServiceManagementController::class, 'attachRelated'])->name('attach-related');

        // Revisions
        Route::get('/{id}/revisions', [ServiceListingController::class, 'revisions'])->name('revisions');
        Route::get('/{id}/revisions/compare', [ServiceListingController::class, 'compareRevisions'])->name('compare-revisions');
        Route::post('/{id}/revisions/restore', [ServiceManagementController::class, 'restoreRevision'])->name('restore-revision');

        // Search Index
        Route::post('/{id}/index', [ServiceManagementController::class, 'indexInSearch'])->name('index-search');
        Route::delete('/{id}/index', [ServiceManagementController::class, 'removeFromIndex'])->name('remove-from-index');
    });
});
