<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Http\Controllers\Api\ProductController;

/*
|--------------------------------------------------------------------------
| Products Module API Routes
|--------------------------------------------------------------------------
*/

// V1 Routes - Full featured ProductController
Route::prefix('api/v1/products')->middleware(['api'])->name('api.products.')->group(function () {
    // Public routes
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/featured', [ProductController::class, 'featured'])->name('featured');
    Route::get('/slug/{slug}', [ProductController::class, 'showBySlug'])->name('slug');
    Route::get('/{id}', [ProductController::class, 'show'])->name('show');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [ProductController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/restore', [ProductController::class, 'restore'])->name('restore');

        // Workflow
        Route::post('/{id}/publish', [ProductController::class, 'publish'])->name('publish');
        Route::post('/{id}/unpublish', [ProductController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [ProductController::class, 'archive'])->name('archive');
        Route::post('/{id}/unarchive', [ProductController::class, 'unarchive'])->name('unarchive');
        Route::post('/{id}/feature', [ProductController::class, 'feature'])->name('feature');
        Route::post('/{id}/unfeature', [ProductController::class, 'unfeature'])->name('unfeature');
        Route::post('/{id}/duplicate', [ProductController::class, 'duplicate'])->name('duplicate');

        // Pricing
        Route::post('/{id}/price', [ProductController::class, 'setPrice'])->name('set-price');
        Route::post('/{id}/sale-price', [ProductController::class, 'setSalePrice'])->name('set-sale-price');
        Route::delete('/{id}/sale-price', [ProductController::class, 'removeSalePrice'])->name('remove-sale-price');

        // Inventory
        Route::patch('/{id}/stock', [ProductController::class, 'updateStock'])->name('update-stock');
        Route::post('/{id}/stock-tracking', [ProductController::class, 'setStockTracking'])->name('set-stock-tracking');
        Route::post('/{id}/backorder', [ProductController::class, 'setBackorderSettings'])->name('set-backorder');
        Route::get('/low-stock', [ProductController::class, 'lowStock'])->name('low-stock');
        Route::get('/out-of-stock', [ProductController::class, 'outOfStock'])->name('out-of-stock');

        // Variants
        Route::post('/{id}/variants', [ProductController::class, 'addVariant'])->name('add-variant');
        Route::put('/{id}/variants/{variantId}', [ProductController::class, 'updateVariant'])->name('update-variant');
        Route::delete('/{id}/variants/{variantId}', [ProductController::class, 'deleteVariant'])->name('delete-variant');

        // Gallery
        Route::post('/{id}/gallery', [ProductController::class, 'addGalleryImage'])->name('add-gallery-image');
        Route::delete('/{id}/gallery/{mediaId}', [ProductController::class, 'removeGalleryImage'])->name('remove-gallery-image');
        Route::post('/{id}/gallery/reorder', [ProductController::class, 'reorderGallery'])->name('reorder-gallery');

        // Relationships
        Route::post('/{id}/categories', [ProductController::class, 'linkCategories'])->name('link-categories');
        Route::post('/{id}/tags', [ProductController::class, 'linkTags'])->name('link-tags');
        Route::post('/{id}/related', [ProductController::class, 'linkRelated'])->name('link-related');
        Route::post('/{id}/upsells', [ProductController::class, 'linkUpsells'])->name('link-upsells');
        Route::post('/{id}/cross-sells', [ProductController::class, 'linkCrossSells'])->name('link-cross-sells');

        // Translations
        Route::post('/{id}/translations', [ProductController::class, 'createTranslation'])->name('create-translation');

        // Bulk Operations
        Route::post('/bulk-update-prices', [ProductController::class, 'bulkUpdatePrices'])->name('bulk-update-prices');
        Route::post('/bulk-update-stock', [ProductController::class, 'bulkUpdateStock'])->name('bulk-update-stock');

        // Import/Export
        Route::post('/import', [ProductController::class, 'import'])->name('import');
        Route::get('/export', [ProductController::class, 'export'])->name('export');
    });
});

