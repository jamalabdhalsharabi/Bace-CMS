<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Currency\Domain\Models\Currency;

class ExchangeRateHistory extends Model
{
    use HasUuids;

    protected $table = 'exchange_rate_history';
    public $timestamps = false;

    protected $fillable = [
        'base_currency_id', 'target_currency_id', 'rate', 'provider', 'recorded_at',
    ];

    protected $casts = [
        'rate' => 'decimal:8',
        'recorded_at' => 'datetime',
    ];

    public function baseCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'base_currency_id');
    }

    public function targetCurrency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'target_currency_id');
    }
}
