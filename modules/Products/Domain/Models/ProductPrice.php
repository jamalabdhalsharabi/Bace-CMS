<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Currency\Domain\Models\Currency;

/**
 * Class ProductPrice
 *
 * Eloquent model representing a product price
 * with currency, comparison pricing, and validity.
 *
 * @package Modules\Products\Domain\Models
 *
 * @property string $id
 * @property string $product_id
 * @property string|null $variant_id
 * @property string $currency_id
 * @property float $amount
 * @property float|null $compare_at_amount
 * @property float|null $cost_amount
 * @property \Carbon\Carbon|null $starts_at
 * @property \Carbon\Carbon|null $ends_at
 *
 * @property-read Product $product Parent product
 * @property-read ProductVariant|null $variant Associated variant
 * @property-read Currency $currency Price currency
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPrice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPrice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductPrice query()
 */
class ProductPrice extends Model
{
    use HasUuids;

    protected $table = 'product_prices';

    protected $fillable = [
        'product_id',
        'variant_id',
        'currency_id',
        'amount',
        'compare_at_amount',
        'cost_amount',
        'starts_at',
        'ends_at',
    ];

    protected $casts = [
        'amount' => 'decimal:4',
        'compare_at_amount' => 'decimal:4',
        'cost_amount' => 'decimal:4',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
    ];

    /**
     * Get the parent product.
     *
     * @return BelongsTo<Product, ProductPrice>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the associated variant.
     *
     * @return BelongsTo<ProductVariant, ProductPrice>
     */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    /**
     * Get the price currency.
     *
     * @return BelongsTo<Currency, ProductPrice>
     */
    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    /**
     * Check if this price represents a sale/discount.
     *
     * @return bool True if compare_at_amount is higher than amount
     */
    public function isOnSale(): bool
    {
        return $this->compare_at_amount !== null && $this->compare_at_amount > $this->amount;
    }

    /**
     * Get the discount percentage if on sale.
     *
     * @return float|null Discount percentage or null if not on sale
     */
    public function getDiscountPercentage(): ?float
    {
        if (!$this->isOnSale()) {
            return null;
        }

        return round((($this->compare_at_amount - $this->amount) / $this->compare_at_amount) * 100, 1);
    }

    /**
     * Check if this price is currently active.
     *
     * @return bool True if within validity period
     */
    public function isActive(): bool
    {
        $now = now();
        if ($this->starts_at && $this->starts_at > $now) {
            return false;
        }
        if ($this->ends_at && $this->ends_at < $now) {
            return false;
        }

        return true;
    }
}
