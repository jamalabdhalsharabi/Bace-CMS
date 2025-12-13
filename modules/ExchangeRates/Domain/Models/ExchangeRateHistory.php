<?php

declare(strict_types=1);

namespace Modules\ExchangeRates\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Currency\Domain\Models\Currency;

/**
 * Class ExchangeRateHistory
 *
 * Eloquent model representing historical exchange rate records
 * for trend analysis and auditing.
 *
 * @package Modules\ExchangeRates\Domain\Models
 *
 * @property string $id
 * @property string $base_currency_id
 * @property string $target_currency_id
 * @property float $rate
 * @property string $provider
 * @property \Carbon\Carbon $recorded_at
 *
 * @property-read \Modules\Currency\Domain\Models\Currency $baseCurrency
 * @property-read \Modules\Currency\Domain\Models\Currency $targetCurrency
 */
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
