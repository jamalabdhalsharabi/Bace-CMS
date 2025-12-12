<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Taxonomy\Traits\HasTaxonomies;

class Event extends Model
{
    use HasUuids, SoftDeletes, HasTaxonomies;

    protected $table = 'events';

    protected $fillable = [
        'status', 'is_featured', 'event_type', 'venue_name', 'venue_address',
        'latitude', 'longitude', 'is_online', 'online_url', 'start_date',
        'end_date', 'timezone', 'max_attendees', 'registration_deadline',
        'is_free', 'featured_image_id', 'meta', 'created_by',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_online' => 'boolean',
        'is_free' => 'boolean',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'max_attendees' => 'integer',
        'meta' => 'array',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(EventTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(EventTranslation::class)->where('locale', app()->getLocale());
    }

    public function ticketTypes(): HasMany
    {
        return $this->hasMany(EventTicketType::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?? $this->translations->first()?->title;
    }

    public function getSlugAttribute(): ?string
    {
        return $this->translation?->slug ?? $this->translations->first()?->slug;
    }

    public function isUpcoming(): bool
    {
        return $this->start_date > now();
    }

    public function isOngoing(): bool
    {
        return $this->start_date <= now() && $this->end_date >= now();
    }

    public function getAvailableSpots(): int
    {
        if (!$this->max_attendees) return PHP_INT_MAX;
        return max(0, $this->max_attendees - $this->registrations()->count());
    }

    public function scopePublished($query) { return $query->where('status', 'published'); }
    public function scopeUpcoming($query) { return $query->where('start_date', '>', now()); }
    public function scopeFeatured($query) { return $query->where('is_featured', true); }
}
