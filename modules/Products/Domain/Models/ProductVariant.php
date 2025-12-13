<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProductVariant
 *
 * Eloquent model representing a product variant
 * with options, pricing, and inventory.
 *
 * @package Modules\Products\Domain\Models
 *
 * @property string $id
 * @property string $product_id
 * @property string $sku
 * @property string|null $barcode
 * @property bool $is_active
 * @property bool $is_default
 * @property string $stock_status
 * @property float|null $weight
 * @property int $sort_order
 * @property array|null $options
 * @property array|null $meta
 *
 * @property-read Product $product Parent product
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductPrice> $prices Variant prices
 * @property-read ProductInventory|null $inventory Inventory record
 * @property-read float|null $price Default currency price (accessor)
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductVariant query()
 */
class ProductVariant extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'product_variants';

    protected $fillable = [
        'product_id',
        'sku',
        'barcode',
        'is_active',
        'is_default',
        'stock_status',
        'weight',
        'sort_order',
        'options',
        'meta',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_default' => 'boolean',
        'weight' => 'decimal:3',
        'sort_order' => 'integer',
        'options' => 'array',
        'meta' => 'array',
    ];

    /**
     * Get the parent product.
     *
     * @return BelongsTo<Product, ProductVariant>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get all prices for this variant.
     *
     * @return HasMany<ProductPrice>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'variant_id');
    }

    /**
     * Get the inventory record for this variant.
     *
     * @return HasOne<ProductInventory>
     */
    public function inventory(): HasOne
    {
        return $this->hasOne(ProductInventory::class, 'variant_id');
    }

    /**
     * Get the price in the default currency.
     *
     * @return float|null The price amount or null
     */
    public function getPriceAttribute(): ?float
    {
        $price = $this->prices()->whereHas('currency', fn ($q) => $q->where('is_default', true))->first();

        return $price?->amount;
    }

    /**
     * Get a specific option value by key.
     *
     * @param string $key The option key (e.g., 'color', 'size')
     * @return string|null The option value or null
     */
    public function getOptionLabel(string $key): ?string
    {
        return $this->options[$key] ?? null;
    }
}
