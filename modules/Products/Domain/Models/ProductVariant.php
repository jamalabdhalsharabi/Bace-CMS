<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'variant_id');
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(ProductInventory::class, 'variant_id');
    }

    public function getPriceAttribute(): ?float
    {
        $price = $this->prices()->whereHas('currency', fn($q) => $q->where('is_default', true))->first();
        return $price?->amount;
    }

    public function getOptionLabel(string $key): ?string
    {
        return $this->options[$key] ?? null;
    }
}
