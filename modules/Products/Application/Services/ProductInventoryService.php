<?php

declare(strict_types=1);

namespace Modules\Products\Application\Services;

use Modules\Products\Domain\Events\StockLevelChanged;
use Modules\Products\Domain\Models\Product;
use Modules\Products\Domain\Repositories\ProductRepository;

/**
 * Product Inventory Service.
 *
 * Manages product stock and inventory operations.
 * Single Responsibility: Inventory management.
 */
final class ProductInventoryService
{
    public function __construct(
        private readonly ProductRepository $repository
    ) {}

    /**
     * Update stock quantity.
     *
     * @param Product $product The product
     * @param int $quantity Quantity to add (positive) or remove (negative)
     * @param string $type Movement type (adjustment, restock, sale, return)
     * @param string|null $reason Reason for the change
     * @return Product
     */
    public function updateStock(Product $product, int $quantity, string $type, ?string $reason = null): Product
    {
        if (!$product->inventory) {
            return $product;
        }

        $previousQuantity = $product->inventory->quantity;
        $product->inventory->adjustStock($quantity, $type);
        $newQuantity = $product->inventory->quantity;

        $this->updateStockStatus($product);

        event(new StockLevelChanged($product, $previousQuantity, $newQuantity, $reason ?? $type));

        return $product->fresh(['inventory']);
    }

    /**
     * Set stock quantity directly.
     *
     * @param Product $product The product
     * @param int $quantity New quantity
     * @return Product
     */
    public function setStock(Product $product, int $quantity): Product
    {
        if (!$product->inventory) {
            return $product;
        }

        $previousQuantity = $product->inventory->quantity;
        $product->inventory->update(['quantity' => $quantity]);
        
        $this->updateStockStatus($product);

        event(new StockLevelChanged($product, $previousQuantity, $quantity, 'manual_set'));

        return $product->fresh(['inventory']);
    }

    /**
     * Reserve stock for an order.
     *
     * @param Product $product The product
     * @param int $quantity Quantity to reserve
     * @return bool
     */
    public function reserveStock(Product $product, int $quantity): bool
    {
        if (!$product->inventory) {
            return false;
        }

        return $product->inventory->reserve($quantity);
    }

    /**
     * Release reserved stock.
     *
     * @param Product $product The product
     * @param int $quantity Quantity to release
     * @return Product
     */
    public function releaseReservation(Product $product, int $quantity): Product
    {
        if ($product->inventory) {
            $product->inventory->releaseReservation($quantity);
        }

        return $product->fresh(['inventory']);
    }

    /**
     * Confirm reserved stock (complete sale).
     *
     * @param Product $product The product
     * @param int $quantity Quantity to confirm
     * @return Product
     */
    public function confirmReservation(Product $product, int $quantity): Product
    {
        if ($product->inventory) {
            $product->inventory->releaseReservation($quantity);
            $product->inventory->adjustStock(-$quantity, 'sale');
            $this->updateStockStatus($product);
        }

        return $product->fresh(['inventory']);
    }

    /**
     * Set low stock threshold.
     *
     * @param Product $product The product
     * @param int $threshold Threshold quantity
     * @return Product
     */
    public function setLowStockThreshold(Product $product, int $threshold): Product
    {
        if ($product->inventory) {
            $product->inventory->update(['low_stock_threshold' => $threshold]);
        }

        return $product->fresh(['inventory']);
    }

    /**
     * Update stock status based on quantity.
     */
    private function updateStockStatus(Product $product): void
    {
        if (!$product->inventory) {
            return;
        }

        $available = $product->inventory->getAvailableQuantity();
        $status = match (true) {
            $available > 0 => 'in_stock',
            $product->allow_backorder => 'on_backorder',
            default => 'out_of_stock',
        };

        $product->update(['stock_status' => $status]);
    }
}
