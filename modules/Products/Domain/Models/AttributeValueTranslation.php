<?php

declare(strict_types=1);

namespace Modules\Products\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AttributeValueTranslation Model - Stores localized attribute value content.
 *
 * This model holds translated names for attribute values
 * in each supported locale (e.g., 'Red' in English, 'أحمر' in Arabic).
 *
 * @property string $id UUID primary key
 * @property string $value_id Foreign key to attribute_values table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $name Translated value display name
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read AttributeValue $value Parent attribute value
 *
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValueTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValueTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AttributeValueTranslation query()
 */
class AttributeValueTranslation extends Model
{
    use HasUuids;

    protected $table = 'attribute_value_translations';

    protected $fillable = [
        'value_id',
        'locale',
        'name',
    ];

    /**
     * Get the attribute value that owns this translation.
     *
     * @return BelongsTo<AttributeValue, AttributeValueTranslation>
     */
    public function value(): BelongsTo
    {
        return $this->belongsTo(AttributeValue::class, 'value_id');
    }
}
