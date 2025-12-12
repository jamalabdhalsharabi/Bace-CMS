<?php

declare(strict_types=1);

namespace Modules\Webhooks\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WebhookLog extends Model
{
    use HasUuids;

    protected $table = 'webhook_logs';

    public $timestamps = false;

    protected $fillable = [
        'webhook_id',
        'event',
        'payload',
        'response_status',
        'response_body',
        'response_time',
        'is_successful',
        'created_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'response_status' => 'integer',
        'response_time' => 'float',
        'is_successful' => 'boolean',
        'created_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::creating(fn($m) => $m->created_at = $m->created_at ?? now());
    }

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    public function scopeSuccessful($query) { return $query->where('is_successful', true); }
    public function scopeFailed($query) { return $query->where('is_successful', false); }
}
