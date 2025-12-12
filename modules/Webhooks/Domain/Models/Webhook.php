<?php

declare(strict_types=1);

namespace Modules\Webhooks\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Http;

class Webhook extends Model
{
    use HasUuids;

    protected $table = 'webhooks';

    protected $fillable = [
        'name',
        'url',
        'secret',
        'events',
        'headers',
        'is_active',
        'last_triggered_at',
        'failure_count',
        'created_by',
    ];

    protected $casts = [
        'events' => 'array',
        'headers' => 'array',
        'is_active' => 'boolean',
        'last_triggered_at' => 'datetime',
        'failure_count' => 'integer',
    ];

    protected $hidden = ['secret'];

    public function logs(): HasMany
    {
        return $this->hasMany(WebhookLog::class)->latest();
    }

    public function trigger(string $event, array $payload): WebhookLog
    {
        $startTime = microtime(true);
        $signature = $this->generateSignature($payload);

        try {
            $response = Http::timeout(config('webhooks.timeout', 30))
                ->withHeaders(array_merge($this->headers ?? [], [
                    'X-Webhook-Signature' => $signature,
                    'X-Webhook-Event' => $event,
                    'Content-Type' => 'application/json',
                ]))
                ->post($this->url, $payload);

            $log = $this->logs()->create([
                'event' => $event,
                'payload' => $payload,
                'response_status' => $response->status(),
                'response_body' => $response->body(),
                'response_time' => (microtime(true) - $startTime) * 1000,
                'is_successful' => $response->successful(),
            ]);

            if ($response->successful()) {
                $this->update(['failure_count' => 0, 'last_triggered_at' => now()]);
            } else {
                $this->increment('failure_count');
            }

            return $log;
        } catch (\Exception $e) {
            $this->increment('failure_count');
            
            return $this->logs()->create([
                'event' => $event,
                'payload' => $payload,
                'response_status' => 0,
                'response_body' => $e->getMessage(),
                'response_time' => (microtime(true) - $startTime) * 1000,
                'is_successful' => false,
            ]);
        }
    }

    public function generateSignature(array $payload): string
    {
        return hash_hmac('sha256', json_encode($payload), $this->secret ?? '');
    }

    public function subscribesTo(string $event): bool
    {
        return in_array($event, $this->events ?? []) || in_array('*', $this->events ?? []);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForEvent($query, string $event)
    {
        return $query->where(function ($q) use ($event) {
            $q->whereJsonContains('events', $event)
              ->orWhereJsonContains('events', '*');
        });
    }
}
