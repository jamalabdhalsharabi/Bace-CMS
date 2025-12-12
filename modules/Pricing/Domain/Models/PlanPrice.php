<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Modules\Currency\Domain\Models\Currency;

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