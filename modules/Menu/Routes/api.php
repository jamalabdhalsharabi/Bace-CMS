<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Api\MenuController;
use Modules\Menu\Http\Controllers\Api\MenuItemController;

Route::prefix('api/v1/menus')->middleware(['api'])->name('api.v1.menus.')->group(function () {
    Route::get('/', [MenuController::class, 'index'])->name('index');
    Route::get('/location/{location}', [MenuController::class, 'byLocation'])->name('location');
    Route::get('/{id}', [MenuController::class, 'show'])->name('show');

    Route::middleware('auth.api')->group(function () {
        Route::post('/', [MenuController::class, 'store'])->name('store');
        Route::put('/{id}', [MenuController::class, 'update'])->name('update');
        Route::delete('/{id}', [MenuController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/activate', [MenuController::class, 'activate'])->name('activate');
        Route::post('/{id}/deactivate', [MenuController::class, 'deactivate'])->name('deactivate');

        Route::post('/{menuId}/items', [MenuItemController::class, 'store'])->name('items.store');
        Route::put('/{menuId}/items/{id}', [MenuItemController::class, 'update'])->name('items.update');
        Route::delete('/{menuId}/items/{id}', [MenuItemController::class, 'destroy'])->name('items.destroy');
        Route::post('/{menuId}/items/reorder', [MenuItemController::class, 'reorder'])->name('items.reorder');
    });
});
