<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * EventSession Model - Represents individual sessions within an event.
 *
 * This model manages event sessions/tracks with scheduling,
 * capacity management, speaker assignments, and localized content.
 *
 * @property string $id UUID primary key
 * @property string $event_id Foreign key to events table
 * @property \Carbon\Carbon $starts_at Session start datetime
 * @property \Carbon\Carbon $ends_at Session end datetime
 * @property string|null $location Session location/room name
 * @property int|null $capacity Maximum number of attendees
 * @property string $status Session status (scheduled, ongoing, completed, cancelled)
 * @property int $sort_order Display order within event
 * @property array|null $meta Additional metadata as JSON
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read Event $event Parent event
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EventSessionTranslation> $translations All translations
 * @property-read EventSessionTranslation|null $translation Current locale translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EventSpeaker> $speakers Assigned speakers
 * @property-read string|null $title Localized session title (accessor)
 * @property-read string|null $description Localized description (accessor)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EventSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventSession query()
 */
class EventSession extends Model
{
    use HasUuids;

    protected $table = 'event_sessions';

    protected $fillable = [
        'event_id',
        'starts_at',
        'ends_at',
        'location',
        'capacity',
        'status',
        'sort_order',
        'meta',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'capacity' => 'integer',
        'sort_order' => 'integer',
        'meta' => 'array',
    ];

    /**
     * Get the parent event.
     *
     * @return BelongsTo<Event, EventSession>
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get all translations for this session.
     *
     * @return HasMany<EventSessionTranslation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(EventSessionTranslation::class, 'session_id');
    }

    /**
     * Get the translation for the current locale.
     *
     * @return HasOne<EventSessionTranslation>
     */
    public function translation(): HasOne
    {
        return $this->hasOne(EventSessionTranslation::class, 'session_id')
            ->where('locale', app()->getLocale());
    }

    /**
     * Get the speakers assigned to this session.
     *
     * @return BelongsToMany<EventSpeaker>
     */
    public function speakers(): BelongsToMany
    {
        return $this->belongsToMany(EventSpeaker::class, 'event_session_speakers', 'session_id', 'speaker_id');
    }

    /**
     * Get the localized session title.
     *
     * @return string|null
     */
    public function getTitleAttribute(): ?string
    {
        return $this->translation?->title ?? $this->translations->first()?->title;
    }

    /**
     * Get the localized session description.
     *
     * @return string|null
     */
    public function getDescriptionAttribute(): ?string
    {
        return $this->translation?->description ?? $this->translations->first()?->description;
    }

    /**
     * Get the session duration in minutes.
     *
     * @return int
     */
    public function getDurationInMinutes(): int
    {
        return $this->starts_at->diffInMinutes($this->ends_at);
    }

    /**
     * Check if the session is currently ongoing.
     *
     * @return bool
     */
    public function isOngoing(): bool
    {
        return now()->between($this->starts_at, $this->ends_at);
    }

    /**
     * Check if the session has ended.
     *
     * @return bool
     */
    public function hasEnded(): bool
    {
        return now()->isAfter($this->ends_at);
    }
}
