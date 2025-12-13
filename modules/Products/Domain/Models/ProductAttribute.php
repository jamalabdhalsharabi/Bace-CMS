<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * ProductAttribute Model - Defines product attributes (size, color, etc.).
 *
 * This model manages configurable product attributes that can be used
 * for filtering, display, and creating product variants.
 *
 * @property string $id UUID primary key
 * @property string $slug Unique attribute identifier (e.g., 'color', 'size')
 * @property string $type Attribute type (select, color, size, text)
 * @property bool $is_filterable Whether shown in product filters
 * @property bool $is_visible Whether shown on product detail page
 * @property bool $is_variation Whether used for creating product variants
 * @property int $sort_order Display order in listings
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, ProductAttributeTranslation> $translations All translations
 * @property-read ProductAttributeTranslation|null $translation Current locale translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AttributeValue> $values Available attribute values
 * @property-read string|null $name Localized attribute name (accessor)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttribute filterable() Filter filterable attributes
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttribute variation() Filter variation attributes
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttribute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttribute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttribute query()
 */
class ProductAttribute extends Model
{
    use HasUuids;

    protected $table = 'product_attributes';

    protected $fillable = [
        'slug',
        'type',
        'is_filterable',
        'is_visible',
        'is_variation',
        'sort_order',
    ];

    protected $casts = [
        'is_filterable' => 'boolean',
        'is_visible' => 'boolean',
        'is_variation' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Get all translations for this attribute.
     *
     * @return HasMany<ProductAttributeTranslation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(ProductAttributeTranslation::class, 'attribute_id');
    }

    /**
     * Get the translation for the current locale.
     *
     * @return HasOne<ProductAttributeTranslation>
     */
    public function translation(): HasOne
    {
        return $this->hasOne(ProductAttributeTranslation::class, 'attribute_id')
            ->where('locale', app()->getLocale());
    }

    /**
     * Get all values for this attribute.
     *
     * @return HasMany<AttributeValue>
     */
    public function values(): HasMany
    {
        return $this->hasMany(AttributeValue::class, 'attribute_id')->orderBy('sort_order');
    }

    /**
     * Get the localized attribute name.
     *
     * @return string|null
     */
    public function getNameAttribute(): ?string
    {
        return $this->translation?->name ?? $this->translations->first()?->name ?? $this->slug;
    }

    /**
     * Find an attribute by its slug.
     *
     * @param string $slug The attribute slug
     * @return self|null
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Scope to filter attributes shown in product filters.
     *
     * @param \Illuminate\Database\Eloquent\Builder<ProductAttribute> $query
     * @return \Illuminate\Database\Eloquent\Builder<ProductAttribute>
     */
    public function scopeFilterable($query)
    {
        return $query->where('is_filterable', true);
    }

    /**
     * Scope to filter attributes used for variants.
     *
     * @param \Illuminate\Database\Eloquent\Builder<ProductAttribute> $query
     * @return \Illuminate\Database\Eloquent\Builder<ProductAttribute>
     */
    public function scopeVariation($query)
    {
        return $query->where('is_variation', true);
    }
}
