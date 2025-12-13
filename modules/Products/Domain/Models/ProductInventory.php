<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class ProductInventory
 *
 * Eloquent model representing product inventory
 * with stock tracking, reservations, and movements.
 *
 * @package Modules\Products\Domain\Models
 *
 * @property string $id
 * @property string $product_id
 * @property string|null $variant_id
 * @property int $quantity
 * @property int $reserved_quantity
 * @property int|null $low_stock_threshold
 *
 * @property-read Product $product Parent product
 * @property-read ProductVariant|null $variant Associated variant
 * @property-read \Illuminate\Database\Eloquent\Collection<int, InventoryMovement> $movements Stock movements
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductInventory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductInventory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductInventory query()
 */
class ProductInventory extends Model
{
    use HasUuids;

    protected $table = 'product_inventories';

    protected $fillable = [
        'product_id',
        'variant_id',
        'quantity',
        'reserved_quantity',
        'low_stock_threshold',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
    ];

    /**
     * Get the parent product.
     *
     * @return BelongsTo<Product, ProductInventory>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the associated variant.
     *
     * @return BelongsTo<ProductVariant, ProductInventory>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get all stock movements for this inventory.
     *
     * @return HasMany<InventoryMovement>
     */
    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_id');
    }

    /**
     * Get the available quantity (total minus reserved).
     *
     * @return int Available quantity for sale
     */
    public function getAvailableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    /**
     * Check if stock is at or below the low stock threshold.
     *
     * @return bool True if low stock
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= ($this->low_stock_threshold ?? 10);
    }

    /**
     * Check if item is out of stock.
     *
     * @return bool True if no available quantity
     */
    public function isOutOfStock(): bool
    {
        return $this->getAvailableQuantity() <= 0;
    }

    /**
     * Reserve stock for an order.
     *
     * @param int $quantity Amount to reserve
     * @return bool True if reservation successful
     */
    public function reserve(int $quantity): bool
    {
        if ($this->getAvailableQuantity() < $quantity) {
            return false;
        }
        $this->increment('reserved_quantity', $quantity);

        return true;
    }

    /**
     * Release a stock reservation.
     *
     * @param int $quantity Amount to release
     * @return void
     */
    public function releaseReservation(int $quantity): void
    {
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
    }

    /**
     * Adjust stock and record the movement.
     *
     * @param int $quantity Amount to adjust (positive or negative)
     * @param string $type Movement type (purchase, sale, adjustment, return)
     * @param string|null $reason Reason for adjustment
     * @param string|null $userId UUID of user making adjustment
     * @return InventoryMovement The created movement record
     */
    public function adjustStock(int $quantity, string $type, ?string $reason = null, ?string $userId = null): InventoryMovement
    {
        $quantityBefore = $this->quantity;
        $this->quantity += $quantity;
        $this->save();

        return $this->movements()->create([
            'type' => $type,
            'quantity' => $quantity,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $this->quantity,
            'reason' => $reason,
            'created_by' => $userId ?? auth()->id(),
        ]);
    }
}
