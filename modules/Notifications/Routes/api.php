<?php

use Illuminate\Support\Facades\Route;
use Modules\Notifications\Http\Controllers\Api\NotificationController;

Route::prefix('api/v1/notifications')->middleware(['api', 'auth:sanctum'])->name('api.v1.notifications.')->group(function () {
    Route::get('/', [NotificationController::class, 'index'])->name('index');
    Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('unread-count');
    Route::get('/{id}', [NotificationController::class, 'show'])->name('show');
    Route::post('/{id}/read', [NotificationController::class, 'markAsRead'])->name('read');
    Route::post('/read-all', [NotificationController::class, 'markAllAsRead'])->name('read-all');
    Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('destroy');
    Route::delete('/clear-read', [NotificationController::class, 'deleteAllRead'])->name('clear-read');
});
