<?php

use Illuminate\Support\Facades\Route;
use Modules\Comments\Http\Controllers\Api\CommentController;

Route::prefix('api/v1/comments')->middleware(['api'])->name('api.comments.')->group(function () {
    // Public routes
    Route::get('/', [CommentController::class, 'index'])->name('index');
    Route::post('/', [CommentController::class, 'store'])->name('store');
    Route::get('/stats', [CommentController::class, 'stats'])->name('stats');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::get('/pending', [CommentController::class, 'pending'])->name('pending');
        Route::get('/{id}', [CommentController::class, 'show'])->name('show');
        Route::put('/{id}', [CommentController::class, 'update'])->name('update');
        Route::delete('/{id}', [CommentController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [CommentController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{parentId}/reply', [CommentController::class, 'reply'])->name('reply');

        // Moderation
        Route::post('/{id}/approve', [CommentController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [CommentController::class, 'reject'])->name('reject');
        Route::post('/{id}/spam', [CommentController::class, 'spam'])->name('spam');
        Route::post('/{id}/not-spam', [CommentController::class, 'notSpam'])->name('not-spam');
        Route::post('/{id}/hide', [CommentController::class, 'hide'])->name('hide');
        Route::post('/{id}/unhide', [CommentController::class, 'unhide'])->name('unhide');
        Route::post('/{id}/report', [CommentController::class, 'report'])->name('report');
        Route::post('/{id}/pin', [CommentController::class, 'pin'])->name('pin');
        Route::post('/{id}/unpin', [CommentController::class, 'unpin'])->name('unpin');
        Route::post('/{id}/vote', [CommentController::class, 'vote'])->name('vote');

        // Bulk operations
        Route::post('/bulk-approve', [CommentController::class, 'bulkApprove'])->name('bulk-approve');
        Route::post('/bulk-reject', [CommentController::class, 'bulkReject'])->name('bulk-reject');
        Route::post('/clean-spam', [CommentController::class, 'cleanSpam'])->name('clean-spam');

        // Locking & Banning
        Route::post('/lock', [CommentController::class, 'lockComments'])->name('lock');
        Route::post('/unlock', [CommentController::class, 'unlockComments'])->name('unlock');
        Route::post('/ban-user', [CommentController::class, 'banUser'])->name('ban-user');
        Route::post('/unban-user', [CommentController::class, 'unbanUser'])->name('unban-user');
    });
});
