<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * AttributeValue Model - Represents possible values for an attribute.
 *
 * This model stores the available values for product attributes
 * like color options, size options, etc.
 *
 * @property string $id UUID primary key
 * @property string $attribute_id Foreign key to product_attributes table
 * @property string $slug Unique value identifier within attribute
 * @property string|null $value Raw value (e.g., '#FF0000' for color)
 * @property int $sort_order Display order within attribute
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read ProductAttribute $attribute Parent attribute definition
 * @property-read \Illuminate\Database\Eloquent\Collection<int, AttributeValueTranslation> $translations All translations
 * @property-read AttributeValueTranslation|null $translation Current locale translation
 * @property-read string $name Localized value name (accessor)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValue newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValue newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValue query()
 */
class AttributeValue extends Model
{
    use HasUuids;

    protected $table = 'attribute_values';

    protected $fillable = [
        'attribute_id',
        'slug',
        'value',
        'sort_order',
    ];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    /**
     * Get the parent attribute.
     *
     * @return BelongsTo<ProductAttribute, AttributeValue>
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }

    /**
     * Get all translations for this value.
     *
     * @return HasMany<AttributeValueTranslation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(AttributeValueTranslation::class, 'value_id');
    }

    /**
     * Get the translation for the current locale.
     *
     * @return HasOne<AttributeValueTranslation>
     */
    public function translation(): HasOne
    {
        return $this->hasOne(AttributeValueTranslation::class, 'value_id')
            ->where('locale', app()->getLocale());
    }

    /**
     * Get the localized value name.
     *
     * @return string
     */
    public function getNameAttribute(): string
    {
        return $this->translation?->name ?? $this->translations->first()?->name ?? $this->slug;
    }
}
