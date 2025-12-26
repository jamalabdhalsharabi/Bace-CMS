<?php

declare(strict_types=1);

namespace Modules\Products\Application\Actions\Inventory;

use Modules\Core\Application\Actions\Action;
use Modules\Products\Domain\Models\Product;
use Modules\Products\Domain\Models\ProductInventory;

/**
 * Update Stock Action.
 *
 * Handles stock quantity adjustments for products.
 */
final class UpdateStockAction extends Action
{
    /**
     * Execute the stock update.
     *
     * @param Product $product The product to update
     * @param int $quantity The quantity to adjust
     * @param string $type The adjustment type (restock, sale, adjustment, return)
     * @param string|null $reason Optional reason for adjustment
     * @return Product The updated product
     */
    public function execute(Product $product, int $quantity, string $type, ?string $reason = null): Product
    {
        $inventory = $product->inventory;
        
        if (!$inventory) {
            $inventory = $product->inventory()->create([
                'quantity' => 0,
                'reserved_quantity' => 0,
                'low_stock_threshold' => 5,
            ]);
        }

        $inventory->adjustStock($quantity, $type, $reason);
        
        return $product->fresh(['inventory']);
    }

    /**
     * Set stock tracking settings.
     *
     * @param Product $product The product to update
     * @param bool $trackStock Whether to track stock
     * @param int|null $lowStockThreshold Optional low stock threshold
     * @return Product The updated product
     */
    public function setTracking(Product $product, bool $trackStock, ?int $lowStockThreshold = null): Product
    {
        $product->update(['track_inventory' => $trackStock]);
        
        if ($lowStockThreshold !== null && $product->inventory) {
            $product->inventory->update(['low_stock_threshold' => $lowStockThreshold]);
        }
        
        return $product->fresh(['inventory']);
    }

    /**
     * Set backorder settings.
     *
     * @param Product $product The product to update
     * @param string $allowBackorders Backorder setting (no, notify, yes)
     * @return Product The updated product
     */
    public function setBackorder(Product $product, string $allowBackorders): Product
    {
        $product->update(['allow_backorder' => $allowBackorders !== 'no']);
        
        return $product->fresh();
    }

    /**
     * Bulk update stock for multiple products.
     *
     * @param array $updates Array of update data
     * @return int Number of products updated
     */
    public function bulkUpdate(array $updates): int
    {
        $count = 0;
        
        foreach ($updates as $update) {
            $product = Product::find($update['id']);
            if ($product) {
                $this->execute(
                    $product,
                    $update['quantity'],
                    $update['type'] ?? 'adjustment',
                    $update['reason'] ?? null
                );
                $count++;
            }
        }
        
        return $count;
    }
}
