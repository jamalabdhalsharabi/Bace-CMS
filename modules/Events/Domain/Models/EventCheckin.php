<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EventCheckin Model - Tracks ticket check-ins.
 *
 * This model records each check-in event for tickets, supporting
 * multiple check-ins per ticket for multi-session events.
 *
 * @property string $id UUID primary key
 * @property string $ticket_id Foreign key to event_tickets table
 * @property string|null $session_id Foreign key to event_sessions (optional)
 * @property string|null $checked_in_by UUID of staff who performed check-in
 * @property string|null $location Physical check-in location/gate
 * @property string|null $method Check-in method (scan, manual, api)
 * @property \Carbon\Carbon $created_at Check-in timestamp
 *
 * @property-read EventTicket $ticket The checked-in ticket
 * @property-read EventSession|null $session Session checked into (if applicable)
 * @property-read \App\Models\User|null $checkedInBy Staff who performed check-in
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EventCheckin newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventCheckin newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventCheckin query()
 */
class EventCheckin extends Model
{
    use HasUuids;

    public $timestamps = false;

    protected $table = 'event_checkins';

    protected $fillable = [
        'ticket_id',
        'session_id',
        'checked_in_by',
        'location',
        'method',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Get the ticket that was checked in.
     *
     * @return BelongsTo<EventTicket, EventCheckin>
     */
    public function ticket(): BelongsTo
    {
        return $this->belongsTo(EventTicket::class);
    }

    /**
     * Get the session this check-in was for.
     *
     * @return BelongsTo<EventSession, EventCheckin>
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(EventSession::class);
    }

    /**
     * Get the staff member who performed the check-in.
     *
     * @return BelongsTo<\App\Models\User, EventCheckin>
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'checked_in_by');
    }
}
