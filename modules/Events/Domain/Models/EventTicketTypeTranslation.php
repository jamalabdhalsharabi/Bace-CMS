<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EventTicketTypeTranslation Model - Stores localized ticket type content.
 *
 * This model holds translated content for event ticket types including
 * names and descriptions in each supported locale.
 *
 * @property string $id UUID primary key
 * @property string $ticket_type_id Foreign key to event_ticket_types table
 * @property string $locale Language code (e.g., 'en', 'ar')
 * @property string $name Translated ticket type name
 * @property string|null $description Translated ticket type description
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read EventTicketType $ticketType Parent ticket type
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketTypeTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketTypeTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicketTypeTranslation query()
 */
class EventTicketTypeTranslation extends Model
{
    use HasUuids;

    protected $table = 'event_ticket_type_translations';

    protected $fillable = [
        'ticket_type_id',
        'locale',
        'name',
        'description',
    ];

    /**
     * Get the ticket type that owns this translation.
     *
     * @return BelongsTo<EventTicketType, EventTicketTypeTranslation>
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(EventTicketType::class, 'ticket_type_id');
    }
}
