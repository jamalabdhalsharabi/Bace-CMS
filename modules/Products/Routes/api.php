<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Http\Controllers\Api\ProductInventoryController;
use Modules\Products\Http\Controllers\Api\ProductListingController;
use Modules\Products\Http\Controllers\Api\ProductManagementController;
use Modules\Products\Http\Controllers\Api\ProductVariantController;

/*
|--------------------------------------------------------------------------
| Products Module API V1 Routes - Feature-Based Controllers
|--------------------------------------------------------------------------
|
| Clean Architecture routes with specialized controllers following
| Single Responsibility Principle.
|
*/

Route::prefix('api/v1/products')->middleware(['api'])->name('api.v1.products.')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Product Listing Routes (Read-Only)
    |--------------------------------------------------------------------------
    */
    Route::get('/', [ProductListingController::class, 'index'])->name('index');
    Route::get('/featured', [ProductListingController::class, 'featured'])->name('featured');
    Route::get('/slug/{slug}', [ProductListingController::class, 'showBySlug'])->name('slug');
    Route::get('/{id}', [ProductListingController::class, 'show'])->name('show');

    /*
    |--------------------------------------------------------------------------
    | Protected Routes
    |--------------------------------------------------------------------------
    */
    Route::middleware('auth.api')->group(function () {

        // Inventory Listing
        Route::get('/inventory/low-stock', [ProductListingController::class, 'lowStock'])->name('low-stock');
        Route::get('/inventory/out-of-stock', [ProductListingController::class, 'outOfStock'])->name('out-of-stock');

        /*
        |--------------------------------------------------------------------------
        | Product Management Routes (CRUD)
        |--------------------------------------------------------------------------
        */
        Route::post('/', [ProductManagementController::class, 'store'])->name('store');
        Route::put('/{id}', [ProductManagementController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductManagementController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [ProductManagementController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/restore', [ProductManagementController::class, 'restore'])->name('restore');
        Route::post('/{id}/publish', [ProductManagementController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [ProductManagementController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [ProductManagementController::class, 'archive'])->name('archive');
        Route::post('/{id}/unarchive', [ProductManagementController::class, 'unarchive'])->name('unarchive');
        Route::post('/{id}/feature', [ProductManagementController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [ProductManagementController::class, 'unfeature'])->name('unfeature');
        Route::post('/{id}/duplicate', [ProductManagementController::class, 'duplicate'])->name('duplicate');
        Route::post('/{id}/translations', [ProductManagementController::class, 'createTranslation'])->name('create-translation');
        Route::post('/import', [ProductManagementController::class, 'import'])->name('import');
        Route::get('/export', [ProductManagementController::class, 'export'])->name('export');

        /*
        |--------------------------------------------------------------------------
        | Product Inventory Routes
        |--------------------------------------------------------------------------
        */
        Route::patch('/{id}/stock', [ProductInventoryController::class, 'updateStock'])->name('update-stock');
        Route::post('/{id}/price', [ProductInventoryController::class, 'setPrice'])->name('set-price');
        Route::post('/{id}/sale-price', [ProductInventoryController::class, 'setSalePrice'])->name('set-sale-price');
        Route::delete('/{id}/sale-price', [ProductInventoryController::class, 'removeSalePrice'])->name('remove-sale-price');
        Route::post('/{id}/stock-tracking', [ProductInventoryController::class, 'setStockTracking'])->name('set-stock-tracking');
        Route::post('/{id}/backorder', [ProductInventoryController::class, 'setBackorderSettings'])->name('set-backorder');
        Route::post('/bulk/prices', [ProductInventoryController::class, 'bulkUpdatePrices'])->name('bulk-update-prices');
        Route::post('/bulk/stock', [ProductInventoryController::class, 'bulkUpdateStock'])->name('bulk-update-stock');

        /*
        |--------------------------------------------------------------------------
        | Product Variant Routes
        |--------------------------------------------------------------------------
        */
        Route::post('/{id}/variants', [ProductVariantController::class, 'addVariant'])->name('add-variant');
        Route::put('/{id}/variants/{variantId}', [ProductVariantController::class, 'updateVariant'])->name('update-variant');
        Route::delete('/{id}/variants/{variantId}', [ProductVariantController::class, 'deleteVariant'])->name('delete-variant');
        Route::post('/{id}/gallery', [ProductVariantController::class, 'addGalleryImage'])->name('add-gallery-image');
        Route::delete('/{id}/gallery/{mediaId}', [ProductVariantController::class, 'removeGalleryImage'])->name('remove-gallery-image');
        Route::post('/{id}/gallery/reorder', [ProductVariantController::class, 'reorderGallery'])->name('reorder-gallery');
        Route::post('/{id}/categories', [ProductVariantController::class, 'linkCategories'])->name('link-categories');
        Route::post('/{id}/tags', [ProductVariantController::class, 'linkTags'])->name('link-tags');
        Route::post('/{id}/related', [ProductVariantController::class, 'linkRelated'])->name('link-related');
        Route::post('/{id}/upsells', [ProductVariantController::class, 'linkUpsells'])->name('link-upsells');
        Route::post('/{id}/cross-sells', [ProductVariantController::class, 'linkCrossSells'])->name('link-cross-sells');
    });
});
