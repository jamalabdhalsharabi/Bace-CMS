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
