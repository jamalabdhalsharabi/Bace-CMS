<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Currency\Domain\Models\Currency;

class RateAlert extends Model
{
    use HasUuids;

    protected $table = 'rate_alerts';

    protected $fillable = [
        'user_id', 'base_currency_id', 'target_currency_id',
        'condition', 'threshold', 'is_active', 'triggered_at', 'notified_at',
    ];

    protected $casts = [
        'threshold' => 'decimal:8',
        'is_active' => 'boolean',
        'triggered_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    public function targetCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'target_currency_id');
    }

    public function checkTrigger(float $currentRate): bool
    {
        return match ($this->condition) {
            'above' => $currentRate >= $this->threshold,
            'below' => $currentRate <= $this->threshold,
            'equals' => abs($currentRate - $this->threshold) < 0.0001,
            default => false,
        };
    }

    public function trigger(): self
    {
        $this->update(['triggered_at' => now()]);
        return $this;
    }

    public function markNotified(): self
    {
        $this->update(['notified_at' => now()]);
        return $this;
    }

    public function deactivate(): self
    {
        $this->update(['is_active' => false]);
        return $this;
    }
}
