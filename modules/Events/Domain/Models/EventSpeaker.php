<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * EventSpeaker Model - Represents event speakers/presenters.
 *
 * This model manages speaker profiles with contact info,
 * social links, avatar images, and localized biographies.
 *
 * @property string $id UUID primary key
 * @property string $name Speaker full name
 * @property string|null $email Speaker email address
 * @property string|null $phone Speaker phone number
 * @property string|null $avatar_id Foreign key to media table
 * @property string|null $company Company/organization name
 * @property string|null $job_title Professional title
 * @property string|null $website Personal/company website URL
 * @property array|null $social_links Social media links as JSON
 * @property bool $is_active Whether speaker is active and visible
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 * @property \Carbon\Carbon|null $deleted_at Soft delete timestamp
 *
 * @property-read \Modules\Media\Domain\Models\Media|null $avatar Speaker avatar image
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EventSpeakerTranslation> $translations All translations
 * @property-read EventSpeakerTranslation|null $translation Current locale translation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EventSession> $sessions Assigned sessions
 * @property-read string|null $bio Localized biography (accessor)
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EventSpeaker active() Filter active speakers
 * @method static \Illuminate\Database\Eloquent\Builder|EventSpeaker newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventSpeaker newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventSpeaker query()
 * @method static \Illuminate\Database\Eloquent\Builder|EventSpeaker onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|EventSpeaker withTrashed()
 */
class EventSpeaker extends Model
{
    use HasUuids;
    use SoftDeletes;

    protected $table = 'event_speakers';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'avatar_id',
        'company',
        'job_title',
        'website',
        'social_links',
        'is_active',
    ];

    protected $casts = [
        'social_links' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the speaker's avatar image.
     *
     * @return BelongsTo<\Modules\Media\Domain\Models\Media, EventSpeaker>
     */
    public function avatar(): BelongsTo
    {
        return $this->belongsTo(\Modules\Media\Domain\Models\Media::class, 'avatar_id');
    }

    /**
     * Get all translations for this speaker.
     *
     * @return HasMany<EventSpeakerTranslation>
     */
    public function translations(): HasMany
    {
        return $this->hasMany(EventSpeakerTranslation::class, 'speaker_id');
    }

    /**
     * Get the translation for the current locale.
     *
     * @return HasOne<EventSpeakerTranslation>
     */
    public function translation(): HasOne
    {
        return $this->hasOne(EventSpeakerTranslation::class, 'speaker_id')
            ->where('locale', app()->getLocale());
    }

    /**
     * Get the sessions this speaker is assigned to.
     *
     * @return BelongsToMany<EventSession>
     */
    public function sessions(): BelongsToMany
    {
        return $this->belongsToMany(EventSession::class, 'event_session_speakers', 'speaker_id', 'session_id');
    }

    /**
     * Get the localized speaker biography.
     *
     * @return string|null
     */
    public function getBioAttribute(): ?string
    {
        return $this->translation?->bio ?? $this->translations->first()?->bio;
    }

    /**
     * Scope to filter only active speakers.
     *
     * @param \Illuminate\Database\Eloquent\Builder<EventSpeaker> $query
     * @return \Illuminate\Database\Eloquent\Builder<EventSpeaker>
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
