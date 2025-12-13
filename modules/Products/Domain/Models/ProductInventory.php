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
 * @property-read Product $product
 * @property-read ProductVariant|null $variant
 * @property-read \Illuminate\Database\Eloquent\Collection|InventoryMovement[] $movements
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class, 'inventory_id');
    }

    public function getAvailableQuantity(): int
    {
        return max(0, $this->quantity - $this->reserved_quantity);
    }

    public function isLowStock(): bool
    {
        return $this->quantity <= ($this->low_stock_threshold ?? 10);
    }

    public function isOutOfStock(): bool
    {
        return $this->getAvailableQuantity() <= 0;
    }

    public function reserve(int $quantity): bool
    {
        if ($this->getAvailableQuantity() < $quantity) {
            return false;
        }
        $this->increment('reserved_quantity', $quantity);
        return true;
    }

    public function releaseReservation(int $quantity): void
    {
        $this->decrement('reserved_quantity', min($quantity, $this->reserved_quantity));
    }

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
