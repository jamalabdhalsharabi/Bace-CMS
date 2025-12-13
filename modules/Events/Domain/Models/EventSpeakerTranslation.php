<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EventSpeakerTranslation Model - Stores localized speaker content.
 *
 * This model holds translated content for event speakers including
 * biographies and expertise descriptions in each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $speaker_id Foreign key to event_speakers table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string|null $bio Translated speaker biography
 * @property string|null $expertise Translated areas of expertise
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read EventSpeaker $speaker Parent speaker
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EventSpeakerTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventSpeakerTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventSpeakerTranslation query()
 */
class EventSpeakerTranslation extends Model
{
    use HasUuids;

    protected $table = 'event_speaker_translations';

    protected $fillable = [
        'speaker_id',
        'locale',
        'bio',
        'expertise',
    ];

    /**
     * Get the speaker that owns this translation.
     *
     * @return BelongsTo<EventSpeaker, EventSpeakerTranslation>
     */
    public function speaker(): BelongsTo
    {
        return $this->belongsTo(EventSpeaker::class, 'speaker_id');
    }
}
