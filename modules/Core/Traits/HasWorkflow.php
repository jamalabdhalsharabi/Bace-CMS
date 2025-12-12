<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

trait HasWorkflow
{
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

    // Status checks
    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isPendingReview(): bool { return $this->status === 'pending_review'; }
    public function isInReview(): bool { return $this->status === 'in_review'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
    public function isPublished(): bool { return $this->status === 'published'; }
    public function isScheduled(): bool { return $this->status === 'scheduled'; }
    public function isUnpublished(): bool { return $this->status === 'unpublished'; }
    public function isArchived(): bool { return $this->status === 'archived'; }

    // Workflow transitions
    public function submitForReview(): self
    {
        $this->update([
            'status' => 'pending_review',
            'submitted_at' => now(),
        ]);
        return $this;
    }

    public function startReview(?string $reviewerId = null): self
    {
        $this->update([
            'status' => 'in_review',
            'reviewed_by' => $reviewerId ?? auth()->id(),
        ]);
        return $this;
    }

    public function approve(?string $notes = null): self
    {
        $this->update([
            'status' => 'approved',
            'review_notes' => $notes,
        ]);
        return $this;
    }

    public function reject(?string $notes = null): self
    {
        $this->update([
            'status' => 'rejected',
            'review_notes' => $notes,
        ]);
        return $this;
    }

    public function publish(): self
    {
        $this->update([
            'status' => 'published',
            'published_at' => now(),
            'scheduled_at' => null,
        ]);
        return $this;
    }

    public function schedule(\DateTimeInterface $date): self
    {
        $this->update([
            'status' => 'scheduled',
            'scheduled_at' => $date,
        ]);
        return $this;
    }

    public function cancelSchedule(): self
    {
        $this->update([
            'status' => 'draft',
            'scheduled_at' => null,
        ]);
        return $this;
    }

    public function unpublish(): self
    {
        $this->update([
            'status' => 'unpublished',
            'published_at' => null,
        ]);
        return $this;
    }

    public function archive(): self
    {
        $this->update([
            'status' => 'archived',
            'archived_at' => now(),
        ]);
        return $this;
    }

    public function unarchive(): self
    {
        $this->update([
            'status' => 'draft',
            'archived_at' => null,
        ]);
        return $this;
    }

    // Scopes
    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingReview($query)
    {
        return $query->where('status', 'pending_review');
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeArchived($query)
    {
        return $query->where('status', 'archived');
    }

    public function scopeReadyToPublish($query)
    {
        return $query->where('status', 'scheduled')
            ->where('scheduled_at', '<=', now());
    }
}
