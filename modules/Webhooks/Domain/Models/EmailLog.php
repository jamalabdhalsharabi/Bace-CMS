<?php

declare(strict_types=1);

namespace Modules\Webhooks\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class EmailLog extends Model
{
    use HasUuids;

    protected $table = 'email_logs';

    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'mailable_type',
        'mailable_id',
        'to',
        'cc',
        'bcc',
        'subject',
        'body',
        'status',
        'error',
        'opened_at',
        'clicked_at',
        'sent_at',
        'created_at',
    ];

    protected $casts = [
        'cc' => 'array',
        'bcc' => 'array',
        'opened_at' => 'datetime',
        'clicked_at' => 'datetime',
        'sent_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn($m) => $m->created_at = $m->created_at ?? now());
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'user_id');
    }

    public function mailable(): MorphTo
    {
        return $this->morphTo();
    }

    public function markAsSent(): self
    {
        $this->update(['status' => 'sent', 'sent_at' => now()]);
        return $this;
    }

    public function markAsOpened(): self
    {
        if (!$this->opened_at) {
            $this->update(['opened_at' => now()]);
        }
        return $this;
    }

    public function scopeStatus($query, string $status) { return $query->where('status', $status); }
    public function scopeSent($query) { return $query->where('status', 'sent'); }
    public function scopeFailed($query) { return $query->where('status', 'failed'); }
}
