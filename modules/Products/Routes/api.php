<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Http\Controllers\Api\ProductControllerV2;

/*
|--------------------------------------------------------------------------
| Products Module API V2 Routes
|--------------------------------------------------------------------------
|
| Clean Architecture routes using specialized services.
|
*/

Route::prefix('api/v2/products')->middleware(['api'])->name('api.v2.products.')->group(function () {
    // Public routes
    Route::get('/', [ProductControllerV2::class, 'index'])->name('index');
    Route::get('/slug/{slug}', [ProductControllerV2::class, 'showBySlug'])->name('slug');
    Route::get('/sku/{sku}', [ProductControllerV2::class, 'showBySku'])->name('sku');
    Route::get('/featured', [ProductControllerV2::class, 'featured'])->name('featured');

    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [ProductControllerV2::class, 'store'])->name('store');
        Route::get('/{id}', [ProductControllerV2::class, 'show'])->name('show');
        Route::put('/{id}', [ProductControllerV2::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductControllerV2::class, 'destroy'])->name('destroy');
        Route::post('/{id}/restore', [ProductControllerV2::class, 'restore'])->name('restore');

        // Workflow
        Route::post('/{id}/publish', [ProductControllerV2::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [ProductControllerV2::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [ProductControllerV2::class, 'archive'])->name('archive');
        Route::post('/{id}/duplicate', [ProductControllerV2::class, 'duplicate'])->name('duplicate');

        // Inventory
        Route::patch('/{id}/stock', [ProductControllerV2::class, 'updateStock'])->name('update-stock');
        Route::put('/{id}/stock', [ProductControllerV2::class, 'setStock'])->name('set-stock');
        Route::post('/{id}/stock/reserve', [ProductControllerV2::class, 'reserveStock'])->name('reserve-stock');
        Route::get('/low-stock', [ProductControllerV2::class, 'lowStock'])->name('low-stock');

        // Pricing
        Route::post('/{id}/price', [ProductControllerV2::class, 'setPrice'])->name('set-price');
        Route::post('/{id}/discount', [ProductControllerV2::class, 'applyDiscount'])->name('apply-discount');
        Route::delete('/{id}/discount', [ProductControllerV2::class, 'removeDiscount'])->name('remove-discount');
    });
});
