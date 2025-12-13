<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * TaxonomyTranslation Model - Stores localized taxonomy content.
 *
 * This model holds translated content for taxonomies including name,
 * slug, description, and SEO metadata for each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $taxonomy_id Foreign key to taxonomies table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $name Translated taxonomy name
 * @property string $slug URL-friendly slug for this locale
 * @property string|null $description Taxonomy description
 * @property string|null $meta_title SEO meta title
 * @property string|null $meta_description SEO meta description
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Taxonomy $taxonomy The parent taxonomy
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyTranslation query()
 */
class TaxonomyTranslation extends Model
{
    use HasUuids;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'taxonomy_translations';

    protected $fillable = [
        'taxonomy_id',
        'locale',
        'name',
        'slug',
        'description',
        'meta_title',
        'meta_description',
    ];

    /**
     * Get the taxonomy that owns this translation.
     *
     * @return BelongsTo<Taxonomy, TaxonomyTranslation>
     */
    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }
}
