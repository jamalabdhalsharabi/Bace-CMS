<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;
use Modules\Products\Domain\Events\ProductCreated;
use Modules\Products\Domain\Events\ProductPublished;
use Modules\Products\Domain\Events\StockLevelChanged;

/**
 * Product Event Subscriber.
 *
 * Handles product-related events.
 */
final class ProductEventSubscriber
{
    /**
     * Handle product created event.
     */
    public function handleProductCreated(ProductCreated $event): void
    {
        Log::info('Product created', [
            'id' => $event->product->id,
            'sku' => $event->product->sku,
        ]);
    }

    /**
     * Handle product published event.
     */
    public function handleProductPublished(ProductPublished $event): void
    {
        Log::info('Product published', [
            'id' => $event->product->id,
            'sku' => $event->product->sku,
        ]);

        // TODO: Update search index, clear caches
    }

    /**
     * Handle stock level changed event.
     */
    public function handleStockLevelChanged(StockLevelChanged $event): void
    {
        Log::info('Stock level changed', [
            'product_id' => $event->product->id,
            'sku' => $event->product->sku,
            'previous' => $event->previousQuantity,
            'new' => $event->newQuantity,
            'reason' => $event->reason,
        ]);

        // Check for low stock alert
        if ($event->product->inventory) {
            $threshold = $event->product->inventory->low_stock_threshold;
            if ($event->newQuantity <= $threshold && $event->previousQuantity > $threshold) {
                // dispatch(new LowStockAlertJob($event->product));
                Log::warning('Low stock alert', [
                    'product_id' => $event->product->id,
                    'sku' => $event->product->sku,
                    'quantity' => $event->newQuantity,
                ]);
            }
        }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            ProductCreated::class => 'handleProductCreated',
            ProductPublished::class => 'handleProductPublished',
            StockLevelChanged::class => 'handleStockLevelChanged',
        ];
    }
}
