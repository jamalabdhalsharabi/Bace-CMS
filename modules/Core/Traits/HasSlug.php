<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the trait.
     */
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateSlug();
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty($model->getSlugSource()) && empty($model->slug)) {
                $model->slug = $model->generateSlug();
            }
        });
    }

    /**
     * Generate a unique slug.
     */
    public function generateSlug(): string
    {
        $source = $this->getSlugSource();
        $slug = Str::slug($this->{$source});
        
        $originalSlug = $slug;
        $counter = 1;

        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if slug exists.
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::where('slug', $slug);

        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        // If using translations, check in translation table
        if (method_exists($this, 'translations')) {
            return $this->translations()
                ->where('slug', $slug)
                ->where('locale', app()->getLocale())
                ->exists();
        }

        return $query->exists();
    }

    /**
     * Get the attribute to generate slug from.
     */
    public function getSlugSource(): string
    {
        return $this->slugSource ?? 'title';
    }

    /**
     * Find model by slug.
     */
    public static function findBySlug(string $slug): ?static
    {
        return static::where('slug', $slug)->first();
    }

    /**
     * Find model by slug or fail.
     */
    public static function findBySlugOrFail(string $slug): static
    {
        return static::where('slug', $slug)->firstOrFail();
    }

    /**
     * Scope: Find by slug.
     */
    public function scopeWhereSlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }
}
