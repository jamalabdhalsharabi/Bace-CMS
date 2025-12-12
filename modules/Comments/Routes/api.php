<?php

use Illuminate\Support\Facades\Route;
use Modules\Comments\Http\Controllers\Api\CommentController;

Route::prefix('api/v1/comments')->middleware(['api'])->name('api.v1.comments.')->group(function () {
    Route::get('/', [CommentController::class, 'index'])->name('index');
    Route::post('/', [CommentController::class, 'store'])->name('store');
    Route::post('/{parentId}/reply', [CommentController::class, 'reply'])->name('reply');
    Route::post('/{id}/report', [CommentController::class, 'report'])->name('report');

    Route::middleware('auth:sanctum')->group(function () {
        // Moderation
        Route::get('/pending', [CommentController::class, 'pending'])->name('pending');
        Route::get('/{id}', [CommentController::class, 'show'])->name('show');
        Route::delete('/{id}', [CommentController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [CommentController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/approve', [CommentController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [CommentController::class, 'reject'])->name('reject');
        Route::post('/{id}/spam', [CommentController::class, 'spam'])->name('spam');
        Route::post('/{id}/not-spam', [CommentController::class, 'confirmNotSpam'])->name('not-spam');

        // Bulk
        Route::post('/bulk/approve', [CommentController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk/reject', [CommentController::class, 'bulkReject'])->name('bulk-reject');
        Route::post('/bulk/delete', [CommentController::class, 'bulkDelete'])->name('bulk-delete');

        // Features
        Route::post('/{id}/pin', [CommentController::class, 'pin'])->name('pin');
        Route::post('/{id}/unpin', [CommentController::class, 'unpin'])->name('unpin');
        Route::post('/{id}/hide', [CommentController::class, 'hide'])->name('hide');
        Route::post('/{id}/unhide', [CommentController::class, 'unhide'])->name('unhide');

        // Voting
        Route::post('/{id}/upvote', [CommentController::class, 'upvote'])->name('upvote');
        Route::post('/{id}/downvote', [CommentController::class, 'downvote'])->name('downvote');
        Route::delete('/{id}/vote', [CommentController::class, 'removeVote'])->name('remove-vote');

        // Reporting
        Route::post('/{id}/dismiss-report', [CommentController::class, 'dismissReport'])->name('dismiss-report');

        // User banning
        Route::post('/ban/{email}', [CommentController::class, 'banUser'])->name('ban-user');
        Route::delete('/ban/{email}', [CommentController::class, 'unbanUser'])->name('unban-user');
    });
});
