<?php

declare(strict_types=1);

namespace Modules\Events\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    use HasUuids;

    protected $table = 'event_registrations';

    protected $fillable = [
        'event_id', 'ticket_type_id', 'user_id', 'attendee_name',
        'attendee_email', 'attendee_phone', 'quantity', 'total_amount',
        'status', 'notes', 'checked_in_at', 'confirmation_code',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'total_amount' => 'decimal:2',
        'checked_in_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(function ($model) {
            $model->confirmation_code = $model->confirmation_code ?? strtoupper(substr(md5(uniqid()), 0, 8));
        });
    }

    public function event(): BelongsTo { return $this->belongsTo(Event::class); }
    public function ticketType(): BelongsTo { return $this->belongsTo(EventTicketType::class, 'ticket_type_id'); }
    public function user(): BelongsTo { return $this->belongsTo(config('auth.providers.users.model')); }

    public function checkIn(): self
    {
        $this->update(['checked_in_at' => now(), 'status' => 'checked_in']);
        return $this;
    }

    public function scopeConfirmed($query) { return $query->where('status', 'confirmed'); }
}
