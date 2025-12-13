<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Coupon
 *
 * Eloquent model representing a discount coupon
 * with usage limits, validity, and plan restrictions.
 *
 * @package Modules\Pricing\Domain\Models
 *
 * @property string $id
 * @property string $code
 * @property string $type
 * @property float $value
 * @property array|null $applies_to_plans
 * @property array|null $applies_to_periods
 * @property int|null $usage_limit
 * @property int $per_user_limit
 * @property int $used_count
 * @property \Carbon\Carbon|null $starts_at
 * @property \Carbon\Carbon|null $expires_at
 * @property bool $first_payment_only
 * @property bool $is_active
 * @property array|null $meta
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|CouponUsage[] $usages
 */
class Coupon extends Model
{
    use HasUuids;

    protected $table = 'coupons';

    protected $fillable = [
        'code', 'type', 'value', 'applies_to_plans', 'applies_to_periods',
        'usage_limit', 'per_user_limit', 'used_count', 'starts_at', 'expires_at',
        'first_payment_only', 'is_active', 'meta',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'applies_to_plans' => 'array',
        'applies_to_periods' => 'array',
        'usage_limit' => 'integer',
        'per_user_limit' => 'integer',
        'used_count' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'first_payment_only' => 'boolean',
        'is_active' => 'boolean',
        'meta' => 'array',
    ];

    public function usages(): HasMany
    {
        return $this->hasMany(CouponUsage::class);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->starts_at && $this->starts_at > now()) return false;
        if ($this->expires_at && $this->expires_at < now()) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        return true;
    }

    public function canBeUsedBy(string $userId): bool
    {
        if (!$this->isValid()) return false;
        if ($this->per_user_limit) {
            $userUsage = $this->usages()->where('user_id', $userId)->count();
            if ($userUsage >= $this->per_user_limit) return false;
        }
        return true;
    }

    public function appliesToPlan(string $planId): bool
    {
        if (empty($this->applies_to_plans)) return true;
        return in_array($planId, $this->applies_to_plans);
    }

    public function calculateDiscount(float $amount): float
    {
        return $this->type === 'percentage'
            ? $amount * ($this->value / 100)
            : min($this->value, $amount);
    }

    public function recordUsage(string $userId, string $subscriptionId): CouponUsage
    {
        $this->increment('used_count');
        return $this->usages()->create([
            'user_id' => $userId,
            'subscription_id' => $subscriptionId,
        ]);
    }

    public static function findByCode(string $code): ?self
    {
        return static::where('code', strtoupper($code))->where('is_active', true)->first();
    }
}
