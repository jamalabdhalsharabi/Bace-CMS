<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Currency\Domain\Models\Currency;

/**
 * Class RateAlert
 *
 * Eloquent model representing a user-defined exchange rate alert
 * with threshold conditions and notification tracking.
 *
 * @package Modules\ExchangeRates\Domain\Models
 *
 * @property string $id
 * @property string $user_id
 * @property string $base_currency_id
 * @property string $target_currency_id
 * @property string $condition
 * @property float $threshold
 * @property bool $is_active
 * @property \Carbon\Carbon|null $triggered_at
 * @property \Carbon\Carbon|null $notified_at
 *
 * @property-read \Modules\Users\Domain\Models\User $user
 * @property-read \Modules\Currency\Domain\Models\Currency $baseCurrency
 * @property-read \Modules\Currency\Domain\Models\Currency $targetCurrency
 */
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
