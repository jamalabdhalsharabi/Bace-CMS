<?php

use Illuminate\Support\Facades\Route;
use Modules\Forms\Http\Controllers\Api\FormController;
use Modules\Forms\Http\Controllers\Api\SubmissionController;

Route::prefix('api/v1/forms')->middleware(['api'])->name('api.forms.')->group(function () {
    Route::get('/slug/{slug}', [FormController::class, 'showBySlug'])->name('slug');
    Route::post('/{id}/submit', [FormController::class, 'submit'])->name('submit');

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/', [FormController::class, 'index'])->name('index');
        Route::post('/', [FormController::class, 'store'])->name('store');
        Route::get('/{id}', [FormController::class, 'show'])->name('show');
        Route::put('/{id}', [FormController::class, 'update'])->name('update');
        Route::delete('/{id}', [FormController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/duplicate', [FormController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/activate', [FormController::class, 'activate'])->name('activate');
        Route::post('/{id}/deactivate', [FormController::class, 'deactivate'])->name('deactivate');
    });
});

Route::prefix('api/v1/submissions')->middleware(['api', 'auth:sanctum'])->name('api.submissions.')->group(function () {
    Route::get('/', [SubmissionController::class, 'index'])->name('index');
    Route::get('/{id}', [SubmissionController::class, 'show'])->name('show');
    Route::put('/{id}/status', [SubmissionController::class, 'updateStatus'])->name('status');
});
