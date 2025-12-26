<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

/**
 * Trait HasWorkflow
 * 
 * Provides workflow state management functionality for Eloquent models.
 * Supports common content states: draft, pending_review, in_review, approved,
 * rejected, published, scheduled, unpublished, and archived.
 * 
 * @package Modules\Core\Traits
 * 
 * @property string $status Current workflow status
 * @property \DateTime|null $published_at Publication timestamp
 * @property \DateTime|null $scheduled_at Scheduled publication timestamp
 * @property \DateTime|null $archived_at Archive timestamp
 * @property \DateTime|null $submitted_at Review submission timestamp
 * @property string|null $reviewed_by ID of the reviewer
 * @property string|null $review_notes Reviewer notes
 * 
 * @method static \Illuminate\Database\Eloquent\Builder withStatus(string $status)
 * @method static \Illuminate\Database\Eloquent\Builder published()
 * @method static \Illuminate\Database\Eloquent\Builder draft()
 * @method static \Illuminate\Database\Eloquent\Builder pendingReview()
 * @method static \Illuminate\Database\Eloquent\Builder scheduled()
 * @method static \Illuminate\Database\Eloquent\Builder archived()
 * @method static \Illuminate\Database\Eloquent\Builder readyToPublish()
 */
trait HasWorkflow
{
    /**
     * Initialize the workflow trait.
     * Adds workflow-related fillable fields and casts.
     *
     * @return void
     */
    public function initializeHasWorkflow(): void
    {
        $this->fillable = array_merge($this->fillable ?? [], [
            'status', 'published_at', 'scheduled_at', 'archived_at',
            'submitted_at', 'reviewed_by', 'review_notes',
        ]);

        $this->casts = array_merge($this->casts ?? [], [
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'archived_at' => 'datetime',
            'submitted_at' => 'datetime',
        ]);
    }

    /**
     * Check if the model is in draft status.
     *
     * @return bool
     */
    public function isDraft(): bool { return $this->status === 'draft'; }

    /**
     * Check if the model is pending review.
     *
     * @return bool
     */
    public function isPendingReview(): bool { return $this->status === 'pending_review'; }

    /**
     * Check if the model is currently being reviewed.
     *
     * @return bool
     */
    public function isInReview(): bool { return $this->status === 'in_review'; }

    /**
     * Check if the model has been approved.
     *
     * @return bool
     */
    public function isApproved(): bool { return $this->status === 'approved'; }

    /**
     * Check if the model has been rejected.
     *
     * @return bool
     */
    public function isRejected(): bool { return $this->status === 'rejected'; }

    /**
     * Check if the model is published.
     *
     * @return bool
     */
    public function isPublished(): bool { return $this->status === 'published'; }

    /**
     * Check if the model is scheduled for future publication.
     *
     * @return bool
     */
    public function isScheduled(): bool { return $this->status === 'scheduled'; }

    /**
     * Check if the model has been unpublished.
     *
     * @return bool
     */
    public function isUnpublished(): bool { return $this->status === 'unpublished'; }

    /**
     * Check if the model is archived.
     *
     * @return bool
     */
    public function isArchived(): bool { return $this->status === 'archived'; }

    /**
     * Submit the model for review.
     * Sets status to 'pending_review' and records submission timestamp.
     *
     * @return self
     */
    public function submitForReview(): self
    {
        $this->update([
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);
        return $this;
    }

    /**
     * Start the review process.
     *
     * @param string|null $reviewerId The reviewer's user ID
     * @return self
     */
    public function startReview(?string $reviewerId = null): self
    {
        $this->update([
            'status' => 'in_review',
            'reviewed_by' => $reviewerId ?? request()->user()?->id,
        ]);
        return $this;
    }

    /**
     * Approve the model after review.
     *
     * @param string|null $notes Optional approval notes
     * @return self
     */
    public function approve(?string $notes = null): self
    {
        $this->update([
            'status' => 'approved',
            'review_notes' => $notes,
        ]);
        return $this;
    }

    /**
     * Reject the model after review.
     *
     * @param string|null $notes Optional rejection reason
     * @return self
     */
    public function reject(?string $notes = null): self
    {
        $this->update([
            'status' => 'rejected',
            'review_notes' => $notes,
        ]);
        return $this;
    }

    /**
     * Publish the model immediately.
     *
     * @return self
     */
    public function publish(): self
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
            'scheduled_at' => null,
        ]);
        return $this;
    }

    /**
     * Schedule the model for future publication.
     *
     * @param \DateTimeInterface $date The scheduled publication date
     * @return self
     */
    public function schedule(\DateTimeInterface $date): self
    {
        $this->update([
            'status' => 'scheduled',
            'scheduled_at' => $date,
        ]);
        return $this;
    }

    /**
     * Cancel scheduled publication and revert to draft.
     *
     * @return self
     */
    public function cancelSchedule(): self
    {
        $this->update([
            'status' => 'draft',
            'scheduled_at' => null,
        ]);
        return $this;
    }

    /**
     * Unpublish the model.
     *
     * @return self
     */
    public function unpublish(): self
    {
        $this->update([
            'status' => 'unpublished',
            'published_at' => null,
        ]);
        return $this;
    }

    /**
     * Archive the model.
     *
     * @return self
     */
    public function archive(): self
    {
        $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
        return $this;
    }

    /**
     * Restore the model from archive to draft.
     *
     * @return self
     */
    public function unarchive(): self
    {
        $this->update([
            'status' => 'draft',
            'archived_at' => null,
        ]);
        return $this;
    }

    /**
     * Scope to filter by specific status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status The status to filter by
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get only published models.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    /**
     * Scope to get only draft models.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    /**
     * Scope to get models pending review.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    /**
     * Scope to get scheduled models.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    /**
     * Scope to get archived models.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    /**
     * Scope to get models ready for automatic publication.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeReadyToPublish($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }
}
