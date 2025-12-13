<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

/**
 * Has Featured Trait.
 *
 * Provides featured functionality for models.
 */
trait HasFeatured
{
    /**
     * Check if the model is featured.
     */
    public function isFeatured(): bool
    {
        return (bool) $this->is_featured;
    }

    /**
     * Feature the model.
     */
    public function feature(): static
    {
        $this->update(['is_featured' => true]);

        return $this;
    }

    /**
     * Unfeature the model.
     */
    public function unfeature(): static
    {
        $this->update(['is_featured' => false]);

        return $this;
    }

    /**
     * Scope to filter only featured models.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to filter non-featured models.
     */
    public function scopeNotFeatured($query)
    {
        return $query->where('is_featured', false);
    }
}
