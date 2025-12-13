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
 * @property-read Product $product
 * @property-read ProductVariant|null $variant
 * @property-read \Modules\Currency\Domain\Models\Currency $currency
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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function isOnSale(): bool
    {
        return $this->compare_at_amount !== null && $this->compare_at_amount > $this->amount;
    }

    public function getDiscountPercentage(): ?float
    {
        if (!$this->isOnSale()) {
            return null;
        }
        return round((($this->compare_at_amount - $this->amount) / $this->compare_at_amount) * 100, 1);
    }

    public function isActive(): bool
    {
        $now = now();
        if ($this->starts_at && $this->starts_at > $now) return false;
        if ($this->ends_at && $this->ends_at < $now) return false;
        return true;
    }
}
