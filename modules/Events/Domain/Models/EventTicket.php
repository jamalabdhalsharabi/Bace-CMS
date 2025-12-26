<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * EventTicket Model - Represents individual event tickets.
 *
 * This model manages event tickets with unique codes, attendee info,
 * check-in tracking, and status management.
 *
 * @property string $id UUID primary key
 * @property string $registration_id Foreign key to event_registrations
 * @property string $ticket_type_id Foreign key to event_ticket_types
 * @property string $ticket_code Unique scannable ticket code
 * @property string $attendee_name Ticket holder's name
 * @property string|null $attendee_email Ticket holder's email
 * @property string $status Ticket status (valid, used, cancelled, expired)
 * @property \Carbon\Carbon|null $checked_in_at When ticket was checked in
 * @property string|null $checked_in_by UUID of staff who checked in
 * @property array|null $meta Additional metadata as JSON
 * @property \Carbon\Carbon $created_at Record creation timestamp
 * @property \Carbon\Carbon|null $updated_at Record last update timestamp
 *
 * @property-read EventRegistration $registration Parent registration order
 * @property-read EventTicketType $ticketType Ticket type definition
 * @property-read \App\Models\User|null $checkedInBy Staff who performed check-in
 * @property-read \Illuminate\Database\Eloquent\Collection<int, EventCheckin> $checkins Check-in history log
 *
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EventTicket query()
 */
class EventTicket extends Model
{
    use HasUuids;

    protected $table = 'event_tickets';

    protected $fillable = [
        'registration_id',
        'ticket_type_id',
        'ticket_code',
        'attendee_name',
        'attendee_email',
        'status',
        'checked_in_at',
        'checked_in_by',
        'meta',
    ];

    protected $casts = [
        'checked_in_at' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * Get the parent registration.
     *
     * @return BelongsTo<EventRegistration, EventTicket>
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class);
    }

    /**
     * Get the ticket type definition.
     *
     * @return BelongsTo<EventTicketType, EventTicket>
     */
    public function ticketType(): BelongsTo
    {
        return $this->belongsTo(EventTicketType::class);
    }

    /**
     * Get the user who performed the check-in.
     *
     * @return BelongsTo<\App\Models\User, EventTicket>
     */
    public function checkedInBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'checked_in_by');
    }

    /**
     * Get all check-in records for this ticket.
     *
     * @return HasMany<EventCheckin>
     */
    public function checkins(): HasMany
    {
        return $this->hasMany(EventCheckin::class, 'ticket_id');
    }

    /**
     * Check in this ticket.
     *
     * @param string|null $userId UUID of staff performing check-in
     * @return self Returns self for method chaining
     */
    public function checkIn(?string $userId = null): self
    {
        $this->update([
            'status' => 'used',
            'checked_in_at' => now(),
            'checked_in_by' => $userId ?? request()->user()?->id,
        ]);
        return $this;
    }

    /**
     * Check if ticket is valid and can be used.
     *
     * @return bool True if ticket status is 'valid'
     */
    public function isValid(): bool
    {
        return $this->status === 'valid';
    }

    /**
     * Check if ticket has been used.
     *
     * @return bool True if ticket has been checked in
     */
    public function isUsed(): bool
    {
        return $this->status === 'used';
    }

    /**
     * Cancel this ticket.
     *
     * @return self Returns self for method chaining
     */
    public function cancel(): self
    {
        $this->update(['status' => 'cancelled']);
        return $this;
    }
}
