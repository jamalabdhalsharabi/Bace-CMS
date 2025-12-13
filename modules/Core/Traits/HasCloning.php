<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Trait HasCloning
 * 
 * Provides model cloning/duplication functionality for Eloquent models.
 * Handles translations, relations, and unique slug generation.
 * 
 * @package Modules\Core\Traits
 * 
 * @property array $excludeFromClone Attributes to exclude when cloning
 * @property array $cloneableRelations Relations to clone with the model
 */
trait HasCloning
{
    /**
     * Create a duplicate of this model.
     * Clones the model with its translations and specified relations.
     *
     * @param string|null $newSlug Optional custom slug for the clone
     * @return static The cloned model
     */
    public function duplicate(?string $newSlug = null): static
    {
        return DB::transaction(function () use ($newSlug) {
            // Clone main model
            $clone = $this->replicate($this->getExcludedCloneAttributes());
            
            // Generate new slug
            if ($newSlug) {
                $clone->slug = $newSlug;
            } elseif (isset($clone->slug)) {
                $clone->slug = $this->generateUniqueSlug($clone->slug);
            }
            
            // Reset status to draft
            if (isset($clone->status)) {
                $clone->status = 'draft';
            }
            
            // Clear published dates
            if (isset($clone->published_at)) {
                $clone->published_at = null;
            }
            if (isset($clone->scheduled_at)) {
                $clone->scheduled_at = null;
            }
            
            $clone->save();
            
            // Clone translations
            if (method_exists($this, 'translations')) {
                foreach ($this->translations as $translation) {
                    $newTrans = $translation->replicate(['id', $this->getForeignKey()]);
                    $newTrans->{$this->getForeignKey()} = $clone->id;
                    if (isset($newTrans->slug)) {
                        $newTrans->slug = $this->generateUniqueSlug($newTrans->slug, $newTrans->locale ?? null);
                    }
                    $newTrans->save();
                }
            }
            
            // Clone relations
            $this->cloneRelations($clone);
            
            return $clone->fresh();
        });
    }

    protected function getExcludedCloneAttributes(): array
    {
        return property_exists($this, 'excludeFromClone') 
            ? $this->excludeFromClone 
            : ['id', 'slug', 'status', 'published_at', 'scheduled_at', 'created_at', 'updated_at'];
    }

    protected function cloneRelations(self $clone): void
    {
        $relations = property_exists($this, 'cloneableRelations') ? $this->cloneableRelations : [];
        
        foreach ($relations as $relation) {
            if (method_exists($this, $relation)) {
                $relatedItems = $this->$relation;
                
                if ($relatedItems instanceof \Illuminate\Database\Eloquent\Collection) {
                    foreach ($relatedItems as $item) {
                        if (method_exists($clone->$relation(), 'attach')) {
                            $clone->$relation()->attach($item->id);
                        } else {
                            $newItem = $item->replicate(['id']);
                            $clone->$relation()->save($newItem);
                        }
                    }
                }
            }
        }
    }

    protected function generateUniqueSlug(string $baseSlug, ?string $locale = null): string
    {
        $slug = $baseSlug . '-copy';
        $count = 1;
        
        while ($this->slugExists($slug, $locale)) {
            $slug = $baseSlug . '-copy-' . $count;
            $count++;
        }
        
        return $slug;
    }

    protected function slugExists(string $slug, ?string $locale = null): bool
    {
        if ($locale && method_exists($this, 'translations')) {
            return $this->translations()
                ->where('locale', $locale)
                ->where('slug', $slug)
                ->exists();
        }
        
        return static::where('slug', $slug)->exists();
    }
}
