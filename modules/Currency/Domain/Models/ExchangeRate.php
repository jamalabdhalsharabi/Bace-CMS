<?php

declare(strict_types=1);

namespace Modules\Currency\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExchangeRate extends Model
{
    use HasUuids;

    protected $table = 'exchange_rates';

    protected $fillable = [
        'from_currency_id',
        'to_currency_id',
        'rate',
        'source',
        'fetched_at',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'fetched_at' => 'datetime',
    ];

    public function fromCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'from_currency_id');
    }

    public function toCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'to_currency_id');
    }

    public function convert(float $amount): float
    {
        return $amount * (float) $this->rate;
    }

    public static function getRate(string $from, string $to): ?self
    {
        $fromCurrency = Currency::findByCode($from);
        $toCurrency = Currency::findByCode($to);

        if (!$fromCurrency || !$toCurrency) {
            return null;
        }

        return static::where('from_currency_id', $fromCurrency->id)
            ->where('to_currency_id', $toCurrency->id)
            ->latest('fetched_at')
            ->first();
    }
}
