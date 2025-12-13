<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Core\Traits\HasTranslations;

/**
 * TaxonomyType Model - Defines taxonomy type configurations.
 *
 * This model represents taxonomy type definitions like categories, tags,
 * or custom classification types with hierarchy and assignment settings.
 *
 * @property string $id UUID primary key
 * @property string $slug Unique type identifier (e.g., 'category', 'tag')
 * @property array $name Localized type names as JSON {locale: name}
 * @property bool $is_hierarchical Whether terms can have parent-child relationships
 * @property bool $is_multiple Whether multiple terms can be assigned
 * @property array|null $applies_to Model classes this type applies to
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Taxonomy> $taxonomies Terms of this type
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TaxonomyType query()
 */
class TaxonomyType extends Model
{
    use HasUuids;
    use HasTranslations;

    public array $translatedAttributes = ['name', 'name_singular', 'description'];
    public string $translationForeignKey = 'type_id';

    protected $table = 'taxonomy_types';

    protected $fillable = [
        'slug',
        'name',
        'is_hierarchical',
        'is_multiple',
        'applies_to',
    ];

    protected $casts = [
        'name' => 'array',
        'is_hierarchical' => 'boolean',
        'is_multiple' => 'boolean',
        'applies_to' => 'array',
    ];

    /**
     * Get all taxonomy terms of this type.
     *
     * @return HasMany<Taxonomy>
     */
    public function taxonomies(): HasMany
    {
        return $this->hasMany(Taxonomy::class, 'type_id');
    }

    /**
     * Check if this type applies to a specific model.
     *
     * @param string $model The model class name
     * @return bool True if type applies to the model
     */
    public function appliesToModel(string $model): bool
    {
        return in_array($model, $this->applies_to ?? [], true);
    }

    /**
     * Find a taxonomy type by its slug.
     *
     * @param string $slug The type slug
     * @return self|null The type or null
     */
    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Get all taxonomy types that apply to a model.
     *
     * @param string $model The model class name
     * @return \Illuminate\Database\Eloquent\Collection<int, self>
     */
    public static function getForModel(string $model): \Illuminate\Database\Eloquent\Collection
    {
        return static::whereJsonContains('applies_to', $model)->get();
    }
}
