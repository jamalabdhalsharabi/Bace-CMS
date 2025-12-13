<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ProductAttributeTranslation Model - Stores localized attribute content.
 *
 * This model holds translated names for product attributes
 * in each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $attribute_id Foreign key to product_attributes table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $name Translated attribute name
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read ProductAttribute $attribute Parent attribute
 *
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttributeTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttributeTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ProductAttributeTranslation query()
 */
class ProductAttributeTranslation extends Model
{
    use HasUuids;

    protected $table = 'product_attribute_translations';

    protected $fillable = [
        'attribute_id',
        'locale',
        'name',
    ];

    /**
     * Get the attribute that owns this translation.
     *
     * @return BelongsTo<ProductAttribute, ProductAttributeTranslation>
     */
    public function attribute(): BelongsTo
    {
        return $this->belongsTo(ProductAttribute::class, 'attribute_id');
    }
}
