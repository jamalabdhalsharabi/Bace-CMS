<?php

use Illuminate\Support\Facades\Route;
use Modules\Webhooks\Http\Controllers\Api\WebhookController;

Route::prefix('api/v1/webhooks')->middleware(['api', 'auth:sanctum'])->name('api.v1.webhooks.')->group(function () {
    Route::get('/', [WebhookController::class, 'index'])->name('index');
    Route::post('/', [WebhookController::class, 'store'])->name('store');
    Route::get('/{id}', [WebhookController::class, 'show'])->name('show');
    Route::put('/{id}', [WebhookController::class, 'update'])->name('update');
    Route::delete('/{id}', [WebhookController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/test', [WebhookController::class, 'test'])->name('test');

    Route::get('/{id}/logs', [WebhookController::class, 'logs'])->name('logs');
    Route::get('/logs/recent', [WebhookController::class, 'recentLogs'])->name('logs.recent');
});
