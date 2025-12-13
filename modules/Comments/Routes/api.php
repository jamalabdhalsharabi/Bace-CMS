<?php

use Illuminate\Support\Facades\Route;
use Modules\Comments\Http\Controllers\Api\CommentController;

Route::prefix('api/v1/comments')->middleware(['api'])->name('api.comments.')->group(function () {
    Route::get('/', [CommentController::class, 'index'])->name('index');
    Route::post('/', [CommentController::class, 'store'])->name('store');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/{id}', [CommentController::class, 'show'])->name('show');
        Route::put('/{id}', [CommentController::class, 'update'])->name('update');
        Route::delete('/{id}', [CommentController::class, 'destroy'])->name('destroy');

        Route::post('/{id}/approve', [CommentController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [CommentController::class, 'reject'])->name('reject');
        Route::post('/{id}/spam', [CommentController::class, 'spam'])->name('spam');
        Route::post('/{id}/report', [CommentController::class, 'report'])->name('report');
    });
});
