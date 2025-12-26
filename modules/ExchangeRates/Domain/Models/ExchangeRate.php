<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Currency\Domain\Models\Currency;

/**
 * Class ExchangeRate
 *
 * Eloquent model representing an exchange rate between currencies
 * with freeze capability and conversion methods.
 *
 * @package Modules\ExchangeRates\Domain\Models
 *
 * @property string $id
 * @property string $base_currency_id
 * @property string $target_currency_id
 * @property float $rate
 * @property float $inverse_rate
 * @property string $provider
 * @property bool $is_frozen
 * @property \Carbon\Carbon|null $frozen_at
 * @property string|null $frozen_by
 * @property \Carbon\Carbon|null $valid_from
 * @property \Carbon\Carbon|null $valid_until
 *
 * @property-read Currency $baseCurrency
 * @property-read Currency $targetCurrency
 */
class ExchangeRate extends Model
{
    use HasUuids;

    protected $table = 'exchange_rates';

    protected $fillable = [
        'base_currency_id', 'target_currency_id', 'rate', 'inverse_rate',
        'provider', 'is_frozen', 'frozen_at', 'frozen_by', 'valid_from', 'valid_until',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'inverse_rate' => 'decimal:8',
        'is_frozen' => 'boolean',
        'frozen_at' => 'datetime',
        'valid_from' => 'datetime',
        'valid_until' => 'datetime',
    ];

    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    public function targetCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'target_currency_id');
    }

    public function frozenBy(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'frozen_by');
    }

    public function freeze(): self
    {
        $this->update([
            'is_frozen' => true,
            'frozen_at' => now(),
            'frozen_by' => request()->user()?->id,
        ]);
        return $this;
    }

    public function unfreeze(): self
    {
        $this->update([
            'is_frozen' => false,
            'frozen_at' => null,
            'frozen_by' => null,
        ]);
        return $this;
    }

    public function convert(float $amount): float
    {
        return round($amount * $this->rate, 2);
    }

    public function inverseConvert(float $amount): float
    {
        return round($amount * $this->inverse_rate, 2);
    }

    public function isActive(): bool
    {
        $now = now();
        if ($this->valid_from && $this->valid_from > $now) return false;
        if ($this->valid_until && $this->valid_until < $now) return false;
        return true;
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('valid_until')->orWhere('valid_until', '>', now());
        })->where(function ($q) {
            $q->whereNull('valid_from')->orWhere('valid_from', '<=', now());
        });
    }

    public function scopeForPair($query, string $baseId, string $targetId)
    {
        return $query->where('base_currency_id', $baseId)->where('target_currency_id', $targetId);
    }

    public function scopeFrozen($query) { return $query->where('is_frozen', true); }
    public function scopeNotFrozen($query) { return $query->where('is_frozen', false); }
}
