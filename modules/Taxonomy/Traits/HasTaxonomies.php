<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Traits;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Modules\Taxonomy\Domain\Models\Taxonomy;
use Modules\Taxonomy\Domain\Models\TaxonomyType;

trait HasTaxonomies
{
    public function taxonomies(): MorphToMany
    {
        return $this->morphToMany(
            Taxonomy::class,
            'taggable',
            'taggables',
            'taggable_id',
            'taxonomy_id'
        );
    }

    public function taxonomiesOfType(string $typeSlug): MorphToMany
    {
        return $this->taxonomies()->whereHas('type', fn ($q) => $q->where('slug', $typeSlug));
    }

    public function categories(): MorphToMany
    {
        return $this->taxonomiesOfType('category');
    }

    public function tags(): MorphToMany
    {
        return $this->taxonomiesOfType('tag');
    }

    public function attachTaxonomy(Taxonomy|string $taxonomy): void
    {
        if (is_string($taxonomy)) {
            $taxonomy = Taxonomy::findBySlug($taxonomy);
        }

        if ($taxonomy) {
            $this->taxonomies()->syncWithoutDetaching($taxonomy);
        }
    }

    public function detachTaxonomy(Taxonomy|string $taxonomy): void
    {
        if (is_string($taxonomy)) {
            $taxonomy = Taxonomy::findBySlug($taxonomy);
        }

        if ($taxonomy) {
            $this->taxonomies()->detach($taxonomy);
        }
    }

    public function syncTaxonomies(array $taxonomyIds, ?string $typeSlug = null): void
    {
        if ($typeSlug) {
            $type = TaxonomyType::findBySlug($typeSlug);
            if ($type) {
                $currentIds = $this->taxonomiesOfType($typeSlug)->pluck('taxonomies.id')->toArray();
                $this->taxonomies()->detach($currentIds);
            }
        }

        $this->taxonomies()->syncWithoutDetaching($taxonomyIds);
    }

    public function hasTaxonomy(Taxonomy|string $taxonomy): bool
    {
        if (is_string($taxonomy)) {
            return $this->taxonomies()->whereHas('translations', fn ($q) => 
                $q->where('slug', $taxonomy)
            )->exists();
        }

        return $this->taxonomies()->where('taxonomies.id', $taxonomy->id)->exists();
    }

    public function hasCategory(string $slug): bool
    {
        return $this->categories()->whereHas('translations', fn ($q) => 
            $q->where('slug', $slug)
        )->exists();
    }

    public function hasTag(string $slug): bool
    {
        return $this->tags()->whereHas('translations', fn ($q) => 
            $q->where('slug', $slug)
        )->exists();
    }

    public function scopeWithTaxonomy($query, string $taxonomySlug)
    {
        return $query->whereHas('taxonomies.translations', fn ($q) => 
            $q->where('slug', $taxonomySlug)
        );
    }

    public function scopeWithCategory($query, string $categorySlug)
    {
        return $query->whereHas('categories.translations', fn ($q) => 
            $q->where('slug', $categorySlug)
        );
    }

    public function scopeWithTag($query, string $tagSlug)
    {
        return $query->whereHas('tags.translations', fn ($q) => 
            $q->where('slug', $tagSlug)
        );
    }
}
