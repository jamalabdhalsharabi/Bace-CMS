<?php

use Illuminate\Support\Facades\Route;
use Modules\Menu\Http\Controllers\Api\MenuController;

Route::prefix('api/v1')->middleware(['api'])->group(function () {
    Route::get('/menus/slug/{slug}', [MenuController::class, 'showBySlug'])->name('api.v1.menus.slug');
    Route::get('/menus/location/{location}', [MenuController::class, 'showByLocation'])->name('api.v1.menus.location');
    Route::get('/menus/{slug}/tree', [MenuController::class, 'tree'])->name('api.v1.menus.tree');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::get('/menus', [MenuController::class, 'index'])->name('api.v1.menus.index');
        Route::post('/menus', [MenuController::class, 'store'])->name('api.v1.menus.store');
        Route::get('/menus/{id}', [MenuController::class, 'show'])->name('api.v1.menus.show');
        Route::put('/menus/{id}', [MenuController::class, 'update'])->name('api.v1.menus.update');
        Route::delete('/menus/{id}', [MenuController::class, 'destroy'])->name('api.v1.menus.destroy');

        // Workflow
        Route::post('/menus/{id}/publish', [MenuController::class, 'publish'])->name('api.v1.menus.publish');
        Route::post('/menus/{id}/unpublish', [MenuController::class, 'unpublish'])->name('api.v1.menus.unpublish');
        Route::post('/menus/{id}/archive', [MenuController::class, 'archive'])->name('api.v1.menus.archive');

        // Location
        Route::put('/menus/{id}/location', [MenuController::class, 'setLocation'])->name('api.v1.menus.set-location');
        Route::delete('/menus/{id}/location', [MenuController::class, 'removeLocation'])->name('api.v1.menus.remove-location');

        // Clone & Preview
        Route::post('/menus/{id}/duplicate', [MenuController::class, 'duplicate'])->name('api.v1.menus.duplicate');
        Route::get('/menus/{id}/preview', [MenuController::class, 'preview'])->name('api.v1.menus.preview');

        // Items Management
        Route::post('/menus/{menuId}/items', [MenuController::class, 'addItem'])->name('api.v1.menus.items.store');
        Route::put('/menu-items/{itemId}', [MenuController::class, 'updateItem'])->name('api.v1.menus.items.update');
        Route::delete('/menu-items/{itemId}', [MenuController::class, 'deleteItem'])->name('api.v1.menus.items.destroy');
        Route::post('/menu-items/reorder', [MenuController::class, 'reorderItems'])->name('api.v1.menus.items.reorder');
        Route::post('/menu-items/{itemId}/toggle', [MenuController::class, 'toggleItem'])->name('api.v1.menus.items.toggle');
        Route::post('/menu-items/{itemId}/move', [MenuController::class, 'moveItem'])->name('api.v1.menus.items.move');

        // Bulk Items
        Route::post('/menus/{menuId}/items/bulk', [MenuController::class, 'bulkAddItems'])->name('api.v1.menus.items.bulk-add');
        Route::delete('/menus/{menuId}/items/bulk', [MenuController::class, 'bulkDeleteItems'])->name('api.v1.menus.items.bulk-delete');

        // Auto-generate
        Route::post('/menus/{menuId}/auto-generate', [MenuController::class, 'autoGenerate'])->name('api.v1.menus.auto-generate');
    });
});
