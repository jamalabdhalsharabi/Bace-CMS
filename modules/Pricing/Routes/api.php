<?php

use Illuminate\Support\Facades\Route;
use Modules\Pricing\Http\Controllers\Api\PlanController;
use Modules\Pricing\Http\Controllers\Api\SubscriptionController;
use Modules\Pricing\Http\Controllers\Api\CouponController;

Route::prefix('api/v1')->middleware(['api'])->group(function () {
    // Public plans
    Route::get('/pricing-plans', [PlanController::class, 'active'])->name('api.v1.plans.active');
    Route::get('/pricing-plans/compare', [PlanController::class, 'compare'])->name('api.v1.plans.compare');
    Route::get('/pricing-plans/slug/{slug}', [PlanController::class, 'showBySlug'])->name('api.v1.plans.slug');

    Route::middleware('auth:sanctum')->group(function () {
        // Admin plans management
        Route::get('/admin/pricing-plans', [PlanController::class, 'index'])->name('api.v1.plans.index');
        Route::post('/admin/pricing-plans', [PlanController::class, 'store'])->name('api.v1.plans.store');
        Route::get('/admin/pricing-plans/{id}', [PlanController::class, 'show'])->name('api.v1.plans.show');
        Route::put('/admin/pricing-plans/{id}', [PlanController::class, 'update'])->name('api.v1.plans.update');
        Route::delete('/admin/pricing-plans/{id}', [PlanController::class, 'destroy'])->name('api.v1.plans.destroy');
        Route::post('/admin/pricing-plans/{id}/activate', [PlanController::class, 'activate'])->name('api.v1.plans.activate');
        Route::post('/admin/pricing-plans/{id}/deactivate', [PlanController::class, 'deactivate'])->name('api.v1.plans.deactivate');
        Route::post('/admin/pricing-plans/{id}/set-default', [PlanController::class, 'setDefault'])->name('api.v1.plans.default');
        Route::post('/admin/pricing-plans/{id}/set-recommended', [PlanController::class, 'setRecommended'])->name('api.v1.plans.recommended');
        Route::post('/admin/pricing-plans/{id}/clone', [PlanController::class, 'clone'])->name('api.v1.plans.clone');
        Route::put('/admin/pricing-plans/reorder', [PlanController::class, 'reorder'])->name('api.v1.plans.reorder');
        Route::get('/admin/pricing-plans/{id}/analytics', [PlanController::class, 'analytics'])->name('api.v1.plans.analytics');
        Route::post('/admin/pricing-plans/export', [PlanController::class, 'export'])->name('api.v1.plans.export');
        Route::post('/admin/pricing-plans/import', [PlanController::class, 'import'])->name('api.v1.plans.import');
        Route::post('/admin/pricing-plans/{id}/link', [PlanController::class, 'link'])->name('api.v1.plans.link');
        Route::delete('/admin/pricing-plans/{id}/link', [PlanController::class, 'unlink'])->name('api.v1.plans.unlink');
        Route::get('/admin/pricing-plans/{id}/links', [PlanController::class, 'links'])->name('api.v1.plans.links');

        // User subscriptions
        Route::get('/subscriptions', [SubscriptionController::class, 'index'])->name('api.v1.subscriptions.index');
        Route::post('/subscriptions', [SubscriptionController::class, 'store'])->name('api.v1.subscriptions.store');
        Route::get('/subscriptions/{id}', [SubscriptionController::class, 'show'])->name('api.v1.subscriptions.show');
        Route::post('/subscriptions/{id}/upgrade', [SubscriptionController::class, 'upgrade'])->name('api.v1.subscriptions.upgrade');
        Route::post('/subscriptions/{id}/downgrade', [SubscriptionController::class, 'downgrade'])->name('api.v1.subscriptions.downgrade');
        Route::post('/subscriptions/{id}/cancel', [SubscriptionController::class, 'cancel'])->name('api.v1.subscriptions.cancel');
        Route::post('/subscriptions/{id}/pause', [SubscriptionController::class, 'pause'])->name('api.v1.subscriptions.pause');
        Route::post('/subscriptions/{id}/resume', [SubscriptionController::class, 'resume'])->name('api.v1.subscriptions.resume');
        Route::post('/subscriptions/{id}/refund', [SubscriptionController::class, 'refund'])->name('api.v1.subscriptions.refund');
        Route::post('/subscriptions/{id}/extend', [SubscriptionController::class, 'extend'])->name('api.v1.subscriptions.extend');

        // Coupons management
        Route::get('/admin/coupons', [CouponController::class, 'index'])->name('api.v1.coupons.index');
        Route::post('/admin/coupons', [CouponController::class, 'store'])->name('api.v1.coupons.store');
        Route::get('/admin/coupons/{id}', [CouponController::class, 'show'])->name('api.v1.coupons.show');
        Route::put('/admin/coupons/{id}', [CouponController::class, 'update'])->name('api.v1.coupons.update');
        Route::delete('/admin/coupons/{id}', [CouponController::class, 'destroy'])->name('api.v1.coupons.destroy');
        Route::post('/admin/coupons/{id}/activate', [CouponController::class, 'activate'])->name('api.v1.coupons.activate');
        Route::post('/admin/coupons/{id}/deactivate', [CouponController::class, 'deactivate'])->name('api.v1.coupons.deactivate');
        
        // Coupon validation (public for authenticated users)
        Route::post('/coupons/validate', [CouponController::class, 'validateCoupon'])->name('api.v1.coupons.validate');

        // Missing Plan Operations: Features, Prices, Translations, Limits, Trial
        Route::post('/admin/pricing-plans/{id}/features', [PlanController::class, 'addFeature'])->name('api.v1.plans.add-feature');
        Route::put('/admin/pricing-plans/{id}/features/{featureId}', [PlanController::class, 'updateFeature'])->name('api.v1.plans.update-feature');
        Route::delete('/admin/pricing-plans/{id}/features/{featureId}', [PlanController::class, 'removeFeature'])->name('api.v1.plans.remove-feature');
        Route::post('/admin/pricing-plans/{id}/features/reorder', [PlanController::class, 'reorderFeatures'])->name('api.v1.plans.reorder-features');
        Route::post('/admin/pricing-plans/{id}/translations', [PlanController::class, 'addTranslation'])->name('api.v1.plans.add-translation');
        Route::put('/admin/pricing-plans/{id}/prices/{currencyId}', [PlanController::class, 'setPrice'])->name('api.v1.plans.set-price');
        Route::post('/admin/pricing-plans/{id}/prices/schedule', [PlanController::class, 'schedulePrice'])->name('api.v1.plans.schedule-price');
        Route::post('/admin/pricing-plans/{id}/discount', [PlanController::class, 'applyDiscount'])->name('api.v1.plans.apply-discount');
        Route::delete('/admin/pricing-plans/{id}/discount', [PlanController::class, 'removeDiscount'])->name('api.v1.plans.remove-discount');
        Route::put('/admin/pricing-plans/{id}/limits', [PlanController::class, 'setUsageLimits'])->name('api.v1.plans.set-limits');
        Route::put('/admin/pricing-plans/{id}/trial', [PlanController::class, 'setTrialPeriod'])->name('api.v1.plans.set-trial');
        Route::post('/admin/pricing-plans/{id}/auto-renewal/enable', [PlanController::class, 'enableAutoRenewal'])->name('api.v1.plans.enable-renewal');
        Route::post('/admin/pricing-plans/{id}/auto-renewal/disable', [PlanController::class, 'disableAutoRenewal'])->name('api.v1.plans.disable-renewal');
    });
});
