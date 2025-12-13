<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EventSessionTranslation Model - Stores localized session content.
 *
 * This model holds translated content for event sessions including
 * titles and descriptions in each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $session_id Foreign key to event_sessions table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $title Translated session title
 * @property string|null $description Translated session description
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read EventSession $session Parent session
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EventSessionTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventSessionTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventSessionTranslation query()
 */
class EventSessionTranslation extends Model
{
    use HasUuids;

    protected $table = 'event_session_translations';

    protected $fillable = [
        'session_id',
        'locale',
        'title',
        'description',
    ];

    /**
     * Get the session that owns this translation.
     *
     * @return BelongsTo<EventSession, EventSessionTranslation>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class, 'session_id');
    }
}
