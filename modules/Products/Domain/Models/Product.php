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

/**
 * Class Product
 *
 * Eloquent model representing a product with translations,
 * variants, pricing, and inventory management.
 *
 * @package Modules\Products\Domain\Models
 *
 * @property string $id
 * @property string $sku
 * @property string|null $barcode
 * @property string $type
 * @property string $status
 * @property string $visibility
 * @property bool $is_featured
 * @property bool $track_inventory
 * @property bool $allow_backorder
 * @property string $stock_status
 * @property bool $requires_shipping
 * @property float|null $weight
 * @property string|null $weight_unit
 * @property string|null $tax_class
 * @property bool $has_variants
 * @property \Carbon\Carbon|null $published_at
 * @property array|null $meta
 * @property array|null $settings
 * @property array|null $dimensions
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|ProductTranslation[] $translations
 * @property-read ProductTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|ProductVariant[] $variants
 * @property-read \Illuminate\Database\Eloquent\Collection|ProductPrice[] $prices
 * @property-read ProductInventory|null $inventory
 * @property-read string|null $name
 * @property-read string|null $slug
 * @property-read float|null $price
 * @property-read int $stock_quantity
 */
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

    /**
     * Define the has-many relationship with product translations.
     *
     * Retrieves all translation records for this product across
     * all supported locales including name, description, and SEO fields.
     *
     * @return HasMany The has-many relationship instance to ProductTranslation
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductTranslation::class);
    }

    /**
     * Define the has-one relationship with the current locale translation.
     *
     * Retrieves the translation record matching the application's
     * current locale setting for displaying localized product content.
     *
     * @return HasOne The has-one relationship instance to ProductTranslation
     */
    public function translation(): HasOne
    {
        return $this->hasOne(ProductTranslation::class)
            ->where('locale', app()->getLocale());
    }

    /**
     * Define the has-many relationship with product variants.
     *
     * Retrieves all variants for this product (size, color combinations)
     * ordered by their sort_order field for consistent display.
     *
     * @return HasMany The has-many relationship instance to ProductVariant
     */
    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class)->orderBy('sort_order');
    }

    /**
     * Define the has-many relationship with product prices.
     *
     * Retrieves all price records for this product across different
     * currencies and billing periods.
     *
     * @return HasMany The has-many relationship instance to ProductPrice
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }

    /**
     * Define the has-one relationship with product inventory.
     *
     * Retrieves the inventory record containing stock quantity,
     * reserved quantity, and low stock threshold settings.
     *
     * @return HasOne The has-one relationship instance to ProductInventory
     */
    public function inventory(): HasOne
    {
        return $this->hasOne(ProductInventory::class);
    }

    /**
     * Define the belongs-to relationship with the product creator.
     *
     * Retrieves the User model who created this product.
     * The author is tracked for audit and ownership purposes.
     *
     * @return BelongsTo The belongs-to relationship instance to User model
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    /**
     * Accessor for the product's localized name.
     *
     * Returns the name from the current locale translation if available,
     * otherwise falls back to the first available translation's name.
     *
     * @return string|null The localized product name or null if no translations exist
     */
    public function getNameAttribute(): ?string
    {
        return $this->translation?->name ?? $this->translations->first()?->name;
    }

    /**
     * Accessor for the product's localized URL slug.
     *
     * Returns the slug from the current locale translation if available,
     * otherwise falls back to the first available translation's slug.
     *
     * @return string|null The localized slug or null if no translations exist
     */
    public function getSlugAttribute(): ?string
    {
        return $this->translation?->slug ?? $this->translations->first()?->slug;
    }

    /**
     * Accessor for the product's localized description.
     *
     * Returns the full HTML description from the current locale
     * translation for displaying on the product detail page.
     *
     * @return string|null The localized description or null if not set
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->translation?->description;
    }

    /**
     * Accessor for the product's price in the default currency.
     *
     * Retrieves the price amount from the price record associated
     * with the system's default currency. Returns null if no price is set.
     *
     * @return float|null The price amount or null if no default price exists
     */
    public function getPriceAttribute(): ?float
    {
        $price = $this->prices()->whereHas('currency', fn($q) => $q->where('is_default', true))->first();
        return $price?->amount;
    }

    /**
     * Accessor for the product's current stock quantity.
     *
     * Returns the quantity from the inventory record.
     * Defaults to 0 if no inventory record exists.
     *
     * @return int The current stock quantity
     */
    public function getStockQuantityAttribute(): int
    {
        return $this->inventory?->quantity ?? 0;
    }

    /**
     * Determine if the product is currently in stock.
     *
     * Checks if the stock_status field equals 'in_stock'.
     * Used for display and purchase eligibility checks.
     *
     * @return bool True if the product is in stock, false otherwise
     */
    public function isInStock(): bool
    {
        return $this->stock_status === 'in_stock';
    }

    /**
     * Query scope to filter only published products.
     *
     * Filters products with 'published' status and where the
     * published_at date is null or in the past. Excludes
     * scheduled future products.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * Query scope to filter only featured products.
     *
     * Filters products where is_featured flag is true.
     * Featured products are highlighted on the storefront.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Query scope to filter only in-stock products.
     *
     * Filters products where stock_status equals 'in_stock'.
     * Useful for hiding out-of-stock items from listings.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeInStock($query)
    {
        return $query->where('stock_status', 'in_stock');
    }

    /**
     * Query scope to filter products by type.
     *
     * Filters products matching the specified type such as
     * 'simple', 'variable', 'digital', 'service', etc.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     * @param string $type The product type to filter by
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Find a product by its translated slug.
     *
     * Searches for a product with a translation matching the
     * given slug in the specified locale (or current locale).
     * Returns null if no matching product is found.
     *
     * @param string $slug The URL slug to search for
     * @param string|null $locale The locale to search in, defaults to current locale
     *
     * @return self|null The matching Product or null if not found
     */
    public static function findBySlug(string $slug, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();
        return static::whereHas('translations', fn($q) => 
            $q->where('slug', $slug)->where('locale', $locale)
        )->first();
    }
}
