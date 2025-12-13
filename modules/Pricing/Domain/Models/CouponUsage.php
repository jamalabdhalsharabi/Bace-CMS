<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Class CouponUsage
 *
 * Eloquent model representing a coupon usage record
 * linking a coupon to a user and subscription.
 *
 * @package Modules\Pricing\Domain\Models
 *
 * @property string $id
 * @property string $coupon_id
 * @property string $user_id
 * @property string $subscription_id
 *
 * @property-read Coupon $coupon
 * @property-read \Modules\Users\Domain\Models\User $user
 * @property-read Subscription $subscription
 */
class CouponUsage extends Model
{
    use HasUuids;

    protected $table = 'coupon_usages';

    protected $fillable = ['coupon_id', 'user_id', 'subscription_id'];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
