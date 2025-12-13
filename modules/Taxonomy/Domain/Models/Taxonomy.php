<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Core\Traits\HasMedia;
use Modules\Core\Traits\HasOrdering;

/**
 * Taxonomy Model - Represents categories, tags, and other taxonomies.
 *
 * This model provides hierarchical classification for content
 * with multi-language support and polymorphic tagging.
 *
 * @property string $id UUID primary key
 * @property string $type_id Foreign key to taxonomy_types
 * @property string|null $parent_id Foreign key to parent taxonomy
 * @property string|null $featured_image_id Foreign key to featured image
 * @property int $ordering Sort order
 * @property bool $is_active Whether taxonomy is active
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read TaxonomyType $type Taxonomy type definition
 * @property-read Taxonomy|null $parent Parent taxonomy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Taxonomy> $children Child taxonomies
 * @property-read \Illuminate\Database\Eloquent\Collection<int, TaxonomyTranslation> $translations All translations
 * @property-read TaxonomyTranslation|null $translation Current locale translation
 * @property-read \Modules\Media\Domain\Models\Media|null $featuredImage Featured image
 * @property-read string|null $name Localized name (accessor)
 * @property-read string|null $slug Localized slug (accessor)
 * @property-read string|null $description Localized description (accessor)
 * @property-read array<int, Taxonomy> $path Ancestor path from root
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy active() Filter active taxonomies
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy root() Filter root-level taxonomies
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy ofType(string $typeSlug) Filter by type
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Taxonomy query()
 */
class Taxonomy extends Model
{
    use HasUuids;
    use SoftDeletes;
    use HasMedia;
    use HasOrdering;

    protected $table = 'taxonomies';

    protected $fillable = [
        'type_id',
        'parent_id',
        'featured_image_id',
        'ordering',
        'is_active',
    ];

    protected $casts = [
        'ordering' => 'integer',
        'is_active' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(TaxonomyType::class, 'type_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->ordered();
    }

    public function featuredImage(): BelongsTo
    {
        return $this->belongsTo(\Modules\Media\Domain\Models\Media::class, 'featured_image_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(TaxonomyTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(TaxonomyTranslation::class)
            ->where('locale', app()->getLocale());
    }

    public function articles(): MorphToMany
    {
        return $this->morphedByMany(
            \Modules\Content\Domain\Models\Article::class,
            'taggable',
            'taggables',
            'taxonomy_id',
            'taggable_id'
        );
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

    public function getPathAttribute(): array
    {
        $path = [$this];
        $parent = $this->parent;

        while ($parent) {
            array_unshift($path, $parent);
            $parent = $parent->parent;
        }

        return $path;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeOfType($query, string $typeSlug)
    {
        return $query->whereHas('type', fn ($q) => $q->where('slug', $typeSlug));
    }

    public static function findBySlug(string $slug, ?string $typeSlug = null, ?string $locale = null): ?self
    {
        $locale = $locale ?? app()->getLocale();

        $query = static::whereHas('translations', fn ($q) => 
            $q->where('slug', $slug)->where('locale', $locale)
        );

        if ($typeSlug) {
            $query->ofType($typeSlug);
        }

        return $query->first();
    }
}
