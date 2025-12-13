<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Currency\Domain\Models\Currency;

/**
 * Class PlanPrice
 *
 * Eloquent model representing a pricing plan price
 * with currency, billing period, and validity.
 *
 * @package Modules\Pricing\Domain\Models
 *
 * @property string $id
 * @property string $plan_id
 * @property string $currency_id
 * @property string $billing_period
 * @property float $amount
 * @property float|null $compare_amount
 * @property float|null $setup_fee
 * @property \Carbon\Carbon|null $effective_from
 * @property \Carbon\Carbon|null $effective_until
 *
 * @property-read PricingPlan $plan
 * @property-read \Modules\Currency\Domain\Models\Currency $currency
 */
class PlanPrice extends Model
{
    use HasUuids;

    protected $table = 'plan_prices';

    protected $fillable = [
        'plan_id', 'currency_id', 'billing_period', 'amount', 'compare_amount',
        'setup_fee', 'effective_from', 'effective_until',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'compare_amount' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'effective_from' => 'datetime',
        'effective_until' => 'datetime',
    ];

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function isOnSale(): bool
    {
        return $this->compare_amount && $this->compare_amount > $this->amount;
    }

    public function getDiscountPercentage(): ?float
    {
        if (!$this->isOnSale()) return null;
        return round((($this->compare_amount - $this->amount) / $this->compare_amount) * 100);
    }

    public function isActive(): bool
    {
        $now = now();
        if ($this->effective_from && $this->effective_from > $now) return false;
        if ($this->effective_until && $this->effective_until < $now) return false;
        return true;
    }
}