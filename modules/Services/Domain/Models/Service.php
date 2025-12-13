<?php

declare(strict_types=1);

namespace Modules\Services\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Service
 *
 * Eloquent model representing a business service offering
 * with translations, workflow, media, and categories.
 *
 * @package Modules\Services\Domain\Models
 *
 * @property string $id
 * @property string $slug
 * @property string $status
 * @property bool $is_featured
 * @property int $sort_order
 * @property string|null $icon
 * @property string|null $color
 * @property \Carbon\Carbon|null $published_at
 * @property \Carbon\Carbon|null $scheduled_at
 * @property \Carbon\Carbon|null $archived_at
 * @property string|null $created_by
 * @property string|null $updated_by
 * @property string|null $reviewed_by
 * @property string|null $review_notes
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|ServiceTranslation[] $translations
 * @property-read ServiceTranslation|null $translation
 * @property-read string|null $name
 * @property-read string|null $description
 */
class Service extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'services';

    protected $fillable = [
        'slug', 'status', 'is_featured', 'sort_order', 'icon', 'color',
        'published_at', 'scheduled_at', 'archived_at',
        'created_by', 'updated_by', 'reviewed_by', 'review_notes',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'published_at' => 'datetime',
        'scheduled_at' => 'datetime',
        'archived_at' => 'datetime',
    ];

    // Translations
    public function translations(): HasMany
    {
        return $this->hasMany(ServiceTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(ServiceTranslation::class)->where('locale', app()->getLocale());
    }

    // Revisions
    public function revisions(): MorphMany
    {
        return $this->morphMany(\Modules\Content\Domain\Models\Revision::class, 'revisionable');
    }

    // Media
    public function media(): MorphMany
    {
        return $this->morphMany(\Modules\Media\Domain\Models\MediaUsage::class, 'usable');
    }

    // Taxonomies
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            \Modules\Taxonomy\Domain\Models\Term::class,
            'service_terms',
            'service_id',
            'term_id'
        );
    }

    // Related Services
    public function relatedServices(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'related_services',
            'service_id',
            'related_service_id'
        );
    }

    // Accessors
    public function getNameAttribute(): ?string
    {
        return $this->translation?->name ?? $this->translations->first()?->name;
    }

    public function getDescriptionAttribute(): ?string
    {
        return $this->translation?->description;
    }

    // Status checks
    public function isDraft(): bool { return $this->status === 'draft'; }
    public function isPendingReview(): bool { return $this->status === 'pending_review'; }
    public function isInReview(): bool { return $this->status === 'in_review'; }
    public function isApproved(): bool { return $this->status === 'approved'; }
    public function isRejected(): bool { return $this->status === 'rejected'; }
    public function isPublished(): bool { return $this->status === 'published'; }
    public function isScheduled(): bool { return $this->status === 'scheduled'; }
    public function isArchived(): bool { return $this->status === 'archived'; }

    // Workflow actions
    public function submitForReview(): self
    {
        $this->update(['status' => 'pending_review']);
        return $this;
    }

    public function startReview(string $reviewerId): self
    {
        $this->update(['status' => 'in_review', 'reviewed_by' => $reviewerId]);
        return $this;
    }

    public function approve(?string $notes = null): self
    {
        $this->update(['status' => 'approved', 'review_notes' => $notes]);
        return $this;
    }

    public function reject(?string $notes = null): self
    {
        $this->update(['status' => 'rejected', 'review_notes' => $notes]);
        return $this;
    }

    public function publish(): self
    {
        $this->update(['status' => 'published', 'published_at' => now(), 'scheduled_at' => null]);
        return $this;
    }

    public function schedule(\DateTime $date): self
    {
        $this->update(['status' => 'scheduled', 'scheduled_at' => $date]);
        return $this;
    }

    public function cancelSchedule(): self
    {
        $this->update(['status' => 'draft', 'scheduled_at' => null]);
        return $this;
    }

    public function unpublish(): self
    {
        $this->update(['status' => 'unpublished', 'published_at' => null]);
        return $this;
    }

    public function archive(): self
    {
        $this->update(['status' => 'archived', 'archived_at' => now()]);
        return $this;
    }

    public function restore(): self
    {
        $this->update(['status' => 'draft', 'archived_at' => null]);
        return $this;
    }

    public function feature(): self
    {
        $this->update(['is_featured' => true]);
        return $this;
    }

    public function unfeature(): self
    {
        $this->update(['is_featured' => false]);
        return $this;
    }

    // Scopes
    public function scopePublished($query) { return $query->where('status', 'published'); }
    public function scopeDraft($query) { return $query->where('status', 'draft'); }
    public function scopePendingReview($query) { return $query->where('status', 'pending_review'); }
    public function scopeArchived($query) { return $query->where('status', 'archived'); }
    public function scopeFeatured($query) { return $query->where('is_featured', true); }
    public function scopeOrdered($query) { return $query->orderBy('sort_order'); }
}
