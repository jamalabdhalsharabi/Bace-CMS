<?php

use Illuminate\Support\Facades\Route;
use Modules\Comments\Http\Controllers\Api\CommentAdminController;
use Modules\Comments\Http\Controllers\Api\CommentBulkController;
use Modules\Comments\Http\Controllers\Api\CommentController;
use Modules\Comments\Http\Controllers\Api\CommentListingController;
use Modules\Comments\Http\Controllers\Api\CommentModerationController;

/*
|--------------------------------------------------------------------------
| Comments API Routes
|--------------------------------------------------------------------------
|
| API routes for the Comments module, organized by responsibility:
| - Listing: Read-only operations for retrieving comments
| - Moderation: Comment approval, rejection, and moderation actions
| - Bulk: Mass operations on multiple comments
| - Admin: Administrative operations (locking, banning, statistics)
| - CRUD: Create, update, delete operations
|
*/

Route::prefix('api/v1/comments')->middleware(['api'])->name('api.v1.comments.')->group(function () {
    // Public routes - Listing
    Route::get('/', [CommentListingController::class, 'index'])->name('index');
    Route::get('/stats', [CommentListingController::class, 'stats'])->name('stats');

    // Authenticated routes
    Route::middleware('auth.api')->group(function () {
        // Listing operations (read-only)
        Route::get('/pending', [CommentListingController::class, 'pending'])->name('pending');
        Route::get('/{id}', [CommentListingController::class, 'show'])->name('show');

        // CRUD operations
        Route::post('/', [CommentController::class, 'store'])->name('store');
        Route::post('/{parentId}/reply', [CommentController::class, 'reply'])->name('reply');
        Route::put('/{id}', [CommentController::class, 'update'])->name('update');
        Route::delete('/{id}', [CommentController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [CommentController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/vote', [CommentController::class, 'vote'])->name('vote');
        Route::post('/{id}/report', [CommentController::class, 'report'])->name('report');

        // Moderation operations
        Route::post('/{id}/approve', [CommentModerationController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [CommentModerationController::class, 'reject'])->name('reject');
        Route::post('/{id}/spam', [CommentModerationController::class, 'spam'])->name('spam');
        Route::post('/{id}/not-spam', [CommentModerationController::class, 'notSpam'])->name('not-spam');
        Route::post('/{id}/hide', [CommentModerationController::class, 'hide'])->name('hide');
        Route::post('/{id}/unhide', [CommentModerationController::class, 'unhide'])->name('unhide');
        Route::post('/{id}/pin', [CommentModerationController::class, 'pin'])->name('pin');
        Route::post('/{id}/unpin', [CommentModerationController::class, 'unpin'])->name('unpin');

        // Bulk operations
        Route::post('/bulk-approve', [CommentBulkController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk-reject', [CommentBulkController::class, 'bulkReject'])->name('bulk-reject');
        Route::post('/bulk-delete', [CommentBulkController::class, 'bulkDelete'])->name('bulk-delete');
        Route::post('/clean-spam', [CommentBulkController::class, 'cleanSpam'])->name('clean-spam');

        // Administrative operations
        Route::post('/lock', [CommentAdminController::class, 'lockComments'])->name('lock');
        Route::post('/unlock', [CommentAdminController::class, 'unlockComments'])->name('unlock');
        Route::post('/ban-user', [CommentAdminController::class, 'banUser'])->name('ban-user');
        Route::post('/unban-user', [CommentAdminController::class, 'unbanUser'])->name('unban-user');
    });
});
