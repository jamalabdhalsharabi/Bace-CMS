<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class TaxonomyTranslation
 *
 * Eloquent model representing a taxonomy translation
 * for multi-language support.
 *
 * @package Modules\Taxonomy\Domain\Models
 *
 * @property string $id
 * @property string $taxonomy_id
 * @property string $locale
 * @property string $name
 * @property string $slug
 * @property string|null $description
 * @property string|null $meta_title
 * @property string|null $meta_description
 *
 * @property-read Taxonomy $taxonomy
 */
class TaxonomyTranslation extends Model
{
    use HasUuids;

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

    public function taxonomy(): BelongsTo
    {
        return $this->belongsTo(Taxonomy::class);
    }
}
