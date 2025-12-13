<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * StockReservation Model - Manages temporary stock holds.
 *
 * This model handles stock reservations for carts and orders,
 * preventing overselling with automatic expiration support.
 *
 * @property string $id UUID primary key
 * @property string $inventory_id Foreign key to product_inventories table
 * @property int $quantity Number of units reserved
 * @property string $reference_type Type of reference (order, cart, quote)
 * @property string $reference_id UUID of the referencing entity
 * @property string $status Reservation status (active, confirmed, released, expired)
 * @property \Carbon\Carbon $expires_at When reservation automatically expires
 * @property \Carbon\Carbon|null $confirmed_at When reservation was confirmed (converted to order)
 * @property \Carbon\Carbon|null $released_at When reservation was manually released
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read ProductInventory $inventory Associated inventory record
 *
 * @method static \Illuminate\Database\Eloquent\Builder|StockReservation active() Filter active non-expired reservations
 * @method static \Illuminate\Database\Eloquent\Builder|StockReservation expired() Filter expired reservations
 * @method static \Illuminate\Database\Eloquent\Builder|StockReservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockReservation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|StockReservation query()
 */
class StockReservation extends Model
{
    use HasUuids;

    protected $table = 'stock_reservations';

    protected $fillable = [
        'inventory_id',
        'quantity',
        'reference_type',
        'reference_id',
        'status',
        'expires_at',
        'confirmed_at',
        'released_at',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'expires_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    /**
     * Get the associated inventory record.
     *
     * @return BelongsTo<ProductInventory, StockReservation>
     */
    public function inventory(): BelongsTo
    {
        return $this->belongsTo(ProductInventory::class, 'inventory_id');
    }

    /**
     * Confirm the reservation (typically when order is placed).
     *
     * @return self Returns self for method chaining
     */
    public function confirm(): self
    {
        $this->update([
            'status' => 'confirmed',
            'confirmed_at' => now(),
        ]);
        return $this;
    }

    /**
     * Release the reservation back to available stock.
     *
     * @return self Returns self for method chaining
     */
    public function release(): self
    {
        $this->update([
            'status' => 'released',
            'released_at' => now(),
        ]);
        return $this;
    }

    /**
     * Check if the reservation has expired.
     *
     * @return bool True if status is active and expires_at is in the past
     */
    public function isExpired(): bool
    {
        return $this->status === 'active' && $this->expires_at->isPast();
    }

    /**
     * Check if the reservation is still active and valid.
     *
     * @return bool True if status is active and not expired
     */
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at->isFuture();
    }

    /**
     * Scope to filter active non-expired reservations.
     *
     * @param \Illuminate\Database\Eloquent\Builder<StockReservation> $query
     * @return \Illuminate\Database\Eloquent\Builder<StockReservation>
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active')->where('expires_at', '>', now());
    }

    /**
     * Scope to filter expired reservations needing cleanup.
     *
     * @param \Illuminate\Database\Eloquent\Builder<StockReservation> $query
     * @return \Illuminate\Database\Eloquent\Builder<StockReservation>
     */
    public function scopeExpired($query)
    {
        return $query->where('status', 'active')->where('expires_at', '<=', now());
    }
}
