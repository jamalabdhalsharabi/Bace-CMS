<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Taxonomy\Traits\HasTaxonomies;

class Product extends Model
{
    use HasUuids;
    use SoftDeletes;
    use HasTaxonomies;

    protected $table = 'products';

    protected $fillable = [
        'sku',
        'barcode',
        'type',
        'status',
        'visibility',
        'is_featured',
        'track_inventory',
        'allow_backorder',
        'stock_status',
        'requires_shipping',
        'weight',
        'weight_unit',
        'tax_class',
        'has_variants',
        'published_at',
        'meta',
        'settings',
        'dimensions',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'track_inventory' => 'boolean',
        'allow_backorder' => 'boolean',
        'requires_shipping' => 'boolean',
        'has_variants' => 'boolean',
        'weight' => 'decimal:3',
        'published_at' => 'datetime',
        'meta' => 'array',
        'settings' => 'array',
        'dimensions' => 'array',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ProductTranslation::class)
            ->where('locale', app()->getLocale());
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(ProductInventory::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function getNameAttribute(): ?string
    {
        return $this->translation?->name ?? $this->translations->first()?->name;
    }

    public function getSlugAttribute(): ?string
    {
        return $this->translation?->slug ?? $this->translations->first()?->slug;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->translation?->description;
    }

    public function getPriceAttribute(): ?float
    {
        $price = $this->prices()->whereHas('currency', fn($q) => $q->where('is_default', true))->first();
        return $price?->amount;
    }

    public function getStockQuantityAttribute(): int
    {
        return $this->inventory?->quantity ?? 0;
    }

    public function isInStock(): bool
    {
        return $this->stock_status === 'in_stock';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock');
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();
        return static::whereHas('translations', fn($q) => 
            $q->where('slug', $slug)->where('locale', $locale)
        )->first();
    }
}
