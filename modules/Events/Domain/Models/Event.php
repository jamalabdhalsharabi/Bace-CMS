<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Taxonomy\Traits\HasTaxonomies;

/**
 * Class Event
 *
 * Eloquent model representing an event with translations,
 * ticket types, registrations, and scheduling.
 *
 * @package Modules\Events\Domain\Models
 *
 * @property string $id
 * @property string $status
 * @property bool $is_featured
 * @property string|null $event_type
 * @property string|null $venue_name
 * @property string|null $venue_address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property bool $is_online
 * @property string|null $online_url
 * @property \Carbon\Carbon $start_date
 * @property \Carbon\Carbon $end_date
 * @property string $timezone
 * @property int|null $max_attendees
 * @property \Carbon\Carbon|null $registration_deadline
 * @property bool $is_free
 * @property string|null $featured_image_id
 * @property array|null $meta
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|EventTranslation[] $translations
 * @property-read EventTranslation|null $translation
 * @property-read \Illuminate\Database\Eloquent\Collection|EventTicketType[] $ticketTypes
 * @property-read \Illuminate\Database\Eloquent\Collection|EventRegistration[] $registrations
 * @property-read string|null $title
 * @property-read string|null $slug
 */
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

    /**
     * Define the has-many relationship with event translations.
     *
     * Retrieves all translation records for this event across
     * all supported locales including title, description, and SEO fields.
     *
     * @return HasMany The has-many relationship instance to EventTranslation
     */
    public function translations(): HasMany
    {
        return $this->hasMany(EventTranslation::class);
    }

    /**
     * Define the has-one relationship with the current locale translation.
     *
     * Retrieves the translation record matching the application's
     * current locale setting for displaying localized event content.
     *
     * @return HasOne The has-one relationship instance to EventTranslation
     */
    public function translation(): HasOne
    {
        return $this->hasOne(EventTranslation::class)->where('locale', app()->getLocale());
    }

    /**
     * Define the has-many relationship with event ticket types.
     *
     * Retrieves all ticket type options available for this event,
     * each with its own pricing, quantity limits, and availability.
     *
     * @return HasMany The has-many relationship instance to EventTicketType
     */
    public function ticketTypes(): HasMany
    {
        return $this->hasMany(EventTicketType::class);
    }

    /**
     * Define the has-many relationship with event registrations.
     *
     * Retrieves all registration records for attendees who have
     * signed up for this event, including their ticket selections.
     *
     * @return HasMany The has-many relationship instance to EventRegistration
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    /**
     * Accessor for the event's localized title.
     *
     * Returns the title from the current locale translation if available,
     * otherwise falls back to the first available translation's title.
     *
     * @return string|null The localized title or null if no translations exist
     */
    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?? $this->translations->first()?->title;
    }

    /**
     * Accessor for the event's localized URL slug.
     *
     * Returns the slug from the current locale translation if available,
     * otherwise falls back to the first available translation's slug.
     *
     * @return string|null The localized slug or null if no translations exist
     */
    public function getSlugAttribute(): ?string
    {
        return $this->translation?->slug ?? $this->translations->first()?->slug;
    }

    /**
     * Determine if the event has not yet started.
     *
     * Compares the event's start_date against the current time.
     * Used to identify future events for filtering and display.
     *
     * @return bool True if the event starts in the future, false otherwise
     */
    public function isUpcoming(): bool
    {
        return $this->start_date > now();
    }

    /**
     * Determine if the event is currently in progress.
     *
     * Checks if the current time falls between the event's
     * start_date and end_date. Used for live event indicators.
     *
     * @return bool True if the event is currently happening, false otherwise
     */
    public function isOngoing(): bool
    {
        return $this->start_date <= now() && $this->end_date >= now();
    }

    /**
     * Calculate the number of available registration spots.
     *
     * Returns the difference between max_attendees and current
     * registration count. Returns PHP_INT_MAX if no limit is set.
     *
     * @return int The number of remaining available spots
     */
    public function getAvailableSpots(): int
    {
        if (!$this->max_attendees) return PHP_INT_MAX;
        return max(0, $this->max_attendees - $this->registrations()->count());
    }

    /**
     * Query scope to filter only published events.
     *
     * Filters events with 'published' status for public display.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopePublished($query) { return $query->where('status', 'published'); }
    /**
     * Query scope to filter only upcoming events.
     *
     * Filters events whose start_date is in the future.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeUpcoming($query) { return $query->where('start_date', '>', now()); }

    /**
     * Query scope to filter only featured events.
     *
     * Filters events where is_featured flag is true.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder instance
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query builder
     */
    public function scopeFeatured($query) { return $query->where('is_featured', true); }
}
