<?php

use Illuminate\Support\Facades\Route;
use Modules\Products\Http\Controllers\Api\ProductController;

Route::prefix('api/v1/products')->middleware(['api'])->name('api.v1.products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/slug/{slug}', [ProductController::class, 'showBySlug'])->name('slug');

    Route::middleware('auth:sanctum')->group(function () {
        // CRUD
        Route::post('/', [ProductController::class, 'store'])->name('store');
        Route::get('/{id}', [ProductController::class, 'show'])->name('show');
        Route::put('/{id}', [ProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [ProductController::class, 'destroy'])->name('destroy');
        Route::delete('/{id}/force', [ProductController::class, 'forceDestroy'])->name('force-destroy');
        Route::post('/{id}/restore', [ProductController::class, 'restore'])->name('restore');

        // Workflow
        Route::post('/{id}/submit-review', [ProductController::class, 'submitForReview'])->name('submit-review');
        Route::post('/{id}/approve', [ProductController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [ProductController::class, 'reject'])->name('reject');
        Route::post('/{id}/publish', [ProductController::class, 'publish'])->name('publish');
        Route::post('/{id}/schedule', [ProductController::class, 'schedule'])->name('schedule');
        Route::post('/{id}/unpublish', [ProductController::class, 'unpublish'])->name('unpublish');
        Route::post('/{id}/archive', [ProductController::class, 'archive'])->name('archive');
        Route::post('/{id}/unarchive', [ProductController::class, 'unarchive'])->name('unarchive');
        Route::post('/{id}/discontinue', [ProductController::class, 'discontinue'])->name('discontinue');

        // Variants
        Route::post('/{id}/variants', [ProductController::class, 'addVariant'])->name('add-variant');
        Route::put('/{id}/variants/{variantId}', [ProductController::class, 'updateVariant'])->name('update-variant');
        Route::delete('/{id}/variants/{variantId}', [ProductController::class, 'deleteVariant'])->name('delete-variant');
        Route::post('/{id}/variants/{variantId}/toggle', [ProductController::class, 'toggleVariant'])->name('toggle-variant');

        // Pricing
        Route::post('/{id}/prices', [ProductController::class, 'setPrice'])->name('set-price');
        Route::put('/{id}/prices/{currencyId}', [ProductController::class, 'updatePrice'])->name('update-price');
        Route::post('/{id}/prices/schedule', [ProductController::class, 'schedulePrice'])->name('schedule-price');
        Route::post('/{id}/discount', [ProductController::class, 'applyDiscount'])->name('apply-discount');
        Route::delete('/{id}/discount', [ProductController::class, 'removeDiscount'])->name('remove-discount');

        // Inventory
        Route::patch('/{id}/stock', [ProductController::class, 'updateStock'])->name('stock');
        Route::post('/{id}/stock/reserve', [ProductController::class, 'reserveStock'])->name('reserve-stock');
        Route::post('/{id}/stock/release', [ProductController::class, 'releaseReservation'])->name('release-stock');
        Route::post('/{id}/stock/confirm', [ProductController::class, 'confirmReservation'])->name('confirm-stock');
        Route::put('/{id}/stock/threshold', [ProductController::class, 'setLowStockThreshold'])->name('stock-threshold');
        Route::post('/{id}/preorder', [ProductController::class, 'enablePreorder'])->name('enable-preorder');

        // Relations
        Route::put('/{id}/categories', [ProductController::class, 'syncCategories'])->name('categories');
        Route::put('/{id}/related', [ProductController::class, 'attachRelated'])->name('related');
        Route::put('/{id}/media', [ProductController::class, 'attachMedia'])->name('media');

        // Clone
        Route::post('/{id}/duplicate', [ProductController::class, 'duplicate'])->name('duplicate');

        // Bulk Operations
        Route::post('/bulk/prices', [ProductController::class, 'bulkUpdatePrices'])->name('bulk-prices');
        Route::post('/bulk/stock', [ProductController::class, 'bulkUpdateStock'])->name('bulk-stock');

        // Import/Export
        Route::post('/import', [ProductController::class, 'import'])->name('import');
        Route::post('/export', [ProductController::class, 'export'])->name('export');
        Route::post('/{id}/sync/{channel}', [ProductController::class, 'syncExternal'])->name('sync-external');
    });
});
