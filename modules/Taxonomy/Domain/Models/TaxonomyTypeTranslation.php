<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TaxonomyTypeTranslation Model - Stores localized taxonomy type content.
 *
 * This model holds translated names and descriptions for taxonomy
 * types in each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $type_id Foreign key to taxonomy_types table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $name Translated type name (plural)
 * @property string|null $name_singular Translated type name (singular)
 * @property string|null $description Translated description
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read TaxonomyType $type The parent taxonomy type
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTypeTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTypeTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTypeTranslation query()
 */
class TaxonomyTypeTranslation extends Model
{
    use HasUuids;

    protected $table = 'taxonomy_type_translations';

    protected $fillable = [
        'type_id',
        'locale',
        'name',
        'name_singular',
        'description',
    ];

    /**
     * Get the taxonomy type that owns this translation.
     *
     * @return BelongsTo<TaxonomyType, TaxonomyTypeTranslation>
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(TaxonomyType::class, 'type_id');
    }
}
