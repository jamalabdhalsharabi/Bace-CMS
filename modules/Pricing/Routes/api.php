<?php

use Illuminate\Support\Facades\Route;
use Modules\Pricing\Http\Controllers\Api\PlanController;

Route::prefix('api/v1/plans')->middleware(['api'])->name('api.plans.')->group(function () {
    Route::get('/', [PlanController::class, 'index'])->name('index');
    Route::get('/{id}', [PlanController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/', [PlanController::class, 'store'])->name('store');
        Route::put('/{id}', [PlanController::class, 'update'])->name('update');
        Route::delete('/{id}', [PlanController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/activate', [PlanController::class, 'activate'])->name('activate');
        Route::post('/{id}/deactivate', [PlanController::class, 'deactivate'])->name('deactivate');
        Route::post('/reorder', [PlanController::class, 'reorder'])->name('reorder');
    });
});
