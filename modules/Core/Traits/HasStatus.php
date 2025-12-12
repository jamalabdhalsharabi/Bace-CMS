<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

trait HasStatus
{
    /**
     * Initialize the trait.
     */
    public function initializeHasStatus(): void
    {
        $this->casts['status'] = 'string';
    }

    /**
     * Get available statuses.
     */
    public static function getStatuses(): array
    {
        return static::$statuses ?? [
            'draft' => 'Draft',
            'pending' => 'Pending Review',
            'published' => 'Published',
            'archived' => 'Archived',
        ];
    }

    /**
     * Get status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return static::getStatuses()[$this->status] ?? $this->status;
    }

    /**
     * Get status color for UI.
     */
    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'gray',
            'pending' => 'yellow',
            'published' => 'green',
            'archived' => 'red',
            default => 'gray',
        };
    }

    /**
     * Check if model has specific status.
     */
    public function hasStatus(string $status): bool
    {
        return $this->status === $status;
    }

    /**
     * Check if model is draft.
     */
    public function isDraft(): bool
    {
        return $this->hasStatus('draft');
    }

    /**
     * Check if model is pending.
     */
    public function isPending(): bool
    {
        return $this->hasStatus('pending');
    }

    /**
     * Check if model is published.
     */
    public function isPublished(): bool
    {
        return $this->hasStatus('published');
    }

    /**
     * Check if model is archived.
     */
    public function isArchived(): bool
    {
        return $this->hasStatus('archived');
    }

    /**
     * Set status to draft.
     */
    public function markAsDraft(): static
    {
        $this->status = 'draft';
        $this->save();
        return $this;
    }

    /**
     * Set status to pending.
     */
    public function markAsPending(): static
    {
        $this->status = 'pending';
        $this->save();
        return $this;
    }

    /**
     * Set status to published.
     */
    public function publish(): static
    {
        $this->status = 'published';
        $this->published_at = $this->published_at ?? now();
        $this->save();
        return $this;
    }

    /**
     * Set status to archived.
     */
    public function archive(): static
    {
        $this->status = 'archived';
        $this->save();
        return $this;
    }

    /**
     * Scope: Filter by status.
     */
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope: Published only.
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where(function ($q) {
                $q->whereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            });
    }

    /**
     * Scope: Draft only.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope: Pending only.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
