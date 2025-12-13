<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

/**
 * Has Publishing Workflow Trait.
 *
 * Provides common publishing workflow methods for models.
 */
trait HasPublishingWorkflow
{
    /**
     * Check if the model is published.
     */
    public function isPublished(): bool
    {
        return $this->status === 'published' && 
               ($this->published_at === null || $this->published_at <= now());
    }

    /**
     * Check if the model is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if the model is scheduled.
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    /**
     * Check if the model is archived.
     */
    public function isArchived(): bool
    {
        return $this->status === 'archived';
    }

    /**
     * Check if the model is pending review.
     */
    public function isPendingReview(): bool
    {
        return $this->status === 'pending_review';
    }

    /**
     * Publish the model.
     */
    public function publish(): static
    {
        $this->update([
            'status' => 'published',
            'published_at' => $this->published_at ?? now(),
        ]);

        return $this;
    }

    /**
     * Unpublish the model.
     */
    public function unpublish(): static
    {
        $this->update(['status' => 'draft']);

        return $this;
    }

    /**
     * Schedule the model for future publication.
     */
    public function schedule(\DateTimeInterface $date): static
    {
        $this->update([
            'status' => 'scheduled',
            'published_at' => $date,
        ]);

        return $this;
    }

    /**
     * Archive the model.
     */
    public function archive(): static
    {
        $this->update(['status' => 'archived']);

        return $this;
    }

    /**
     * Submit for review.
     */
    public function submitForReview(): static
    {
        $this->update(['status' => 'pending_review']);

        return $this;
    }

    /**
     * Scope to filter only published models.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(fn ($q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    /**
     * Scope to filter only draft models.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to filter only scheduled models.
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }
}
