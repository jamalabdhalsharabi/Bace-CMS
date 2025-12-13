<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Class Subscription
 *
 * Eloquent model representing a user subscription to a pricing plan
 * with billing, status management, and usage tracking.
 *
 * @package Modules\Pricing\Domain\Models
 *
 * @property string $id
 * @property string $user_id
 * @property string $plan_id
 * @property string $billing_period
 * @property string $status
 * @property \Carbon\Carbon|null $trial_ends_at
 * @property \Carbon\Carbon $starts_at
 * @property \Carbon\Carbon $ends_at
 * @property \Carbon\Carbon|null $cancelled_at
 * @property string|null $cancel_reason
 * @property \Carbon\Carbon|null $paused_at
 * @property \Carbon\Carbon|null $resume_at
 * @property string|null $pending_plan_id
 * @property string|null $payment_method
 * @property string|null $external_id
 * @property array|null $meta
 *
 * @property-read \Modules\Users\Domain\Models\User $user
 * @property-read PricingPlan $plan
 * @property-read PricingPlan|null $pendingPlan
 * @property-read \Illuminate\Database\Eloquent\Collection|SubscriptionUsage[] $usages
 */
class Subscription extends Model
{
    use HasUuids;

    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id', 'plan_id', 'billing_period', 'status', 'trial_ends_at',
        'starts_at', 'ends_at', 'cancelled_at', 'cancel_reason', 'paused_at',
        'resume_at', 'pending_plan_id', 'payment_method', 'external_id', 'meta',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'paused_at' => 'datetime',
        'resume_at' => 'datetime',
        'meta' => 'array',
    ];

    /**
     * Define the belongs-to relationship with the subscriber.
     *
     * Retrieves the User model who owns this subscription.
     * Used for identifying the account holder and permissions.
     *
     * @return BelongsTo The belongs-to relationship instance to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    /**
     * Define the belongs-to relationship with the pricing plan.
     *
     * Retrieves the PricingPlan model this subscription is based on.
     * Contains features, limits, and pricing information.
     *
     * @return BelongsTo The belongs-to relationship instance to PricingPlan
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }

    /**
     * Define the belongs-to relationship with a pending plan change.
     *
     * Retrieves the PricingPlan the user is scheduled to switch to
     * at the end of the current billing period.
     *
     * @return BelongsTo The belongs-to relationship instance to PricingPlan
     */
    public function pendingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'pending_plan_id');
    }

    /**
     * Define the has-many relationship with subscription usage records.
     *
     * Retrieves all usage tracking records for metered resources
     * like API calls, storage, or seats.
     *
     * @return HasMany The has-many relationship instance to SubscriptionUsage
     */
    public function usages(): HasMany
    {
        return $this->hasMany(SubscriptionUsage::class);
    }

    /**
     * Determine if the subscription is currently active.
     *
     * @return bool True if status is 'active', false otherwise
     */
    public function isActive(): bool { return $this->status === 'active'; }

    /**
     * Determine if the subscription is on a trial period.
     *
     * @return bool True if on trial and trial hasn't expired, false otherwise
     */
    public function isOnTrial(): bool { return $this->status === 'trial' && $this->trial_ends_at > now(); }

    /**
     * Determine if the subscription is currently paused.
     *
     * @return bool True if status is 'paused', false otherwise
     */
    public function isPaused(): bool { return $this->status === 'paused'; }

    /**
     * Determine if the subscription has been cancelled.
     *
     * @return bool True if status is 'cancelled', false otherwise
     */
    public function isCancelled(): bool { return $this->status === 'cancelled'; }

    /**
     * Determine if the subscription has expired.
     *
     * @return bool True if expired or past end date, false otherwise
     */
    public function isExpired(): bool { return $this->status === 'expired' || ($this->ends_at && $this->ends_at < now()); }

    /**
     * Cancel the subscription.
     *
     * Sets the status to 'cancelled', records the cancellation timestamp,
     * and optionally stores the cancellation reason for analytics.
     *
     * @param string|null $reason The reason for cancellation
     *
     * @return self The current Subscription instance for method chaining
     */
    public function cancel(?string $reason = null): self
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);
        return $this;
    }

    /**
     * Pause the subscription temporarily.
     *
     * Sets the status to 'paused' and records when the pause began.
     * Optionally schedules an automatic resume date.
     *
     * @param string|null $resumeAt Optional date/time to auto-resume
     *
     * @return self The current Subscription instance for method chaining
     */
    public function pause(?string $resumeAt = null): self
    {
        $this->update([
            'status' => 'paused',
            'paused_at' => now(),
            'resume_at' => $resumeAt ? \Carbon\Carbon::parse($resumeAt) : null,
        ]);
        return $this;
    }

    /**
     * Resume a paused subscription.
     *
     * Reactivates the subscription by setting status to 'active'
     * and clearing the pause/resume timestamps.
     *
     * @return self The current Subscription instance for method chaining
     */
    public function resume(): self
    {
        $this->update(['status' => 'active', 'paused_at' => null, 'resume_at' => null]);
        return $this;
    }

    /**
     * Extend the subscription by a number of days.
     *
     * Adds the specified number of days to the current end date.
     * Useful for promotions, credits, or compensation.
     *
     * @param int $days The number of days to add
     *
     * @return self The current Subscription instance for method chaining
     */
    public function extend(int $days): self
    {
        $this->update(['ends_at' => $this->ends_at->addDays($days)]);
        return $this;
    }

    /**
     * Check if the subscription's plan includes a feature.
     *
     * Delegates to the associated plan to check feature availability.
     *
     * @param string $key The feature key to check
     *
     * @return bool True if the feature is included, false otherwise
     */
    public function hasFeature(string $key): bool
    {
        return $this->plan->hasFeature($key);
    }

    /**
     * Get a resource limit from the subscription's plan.
     *
     * Delegates to the associated plan to retrieve limit values.
     *
     * @param string $resource The resource identifier
     *
     * @return int|null The limit value or null if unlimited
     */
    public function getLimit(string $resource): ?int
    {
        return $this->plan->getLimit($resource);
    }

    /**
     * Record usage of a metered resource.
     *
     * Creates a usage record for the current billing period.
     * Used for metered billing and limit enforcement.
     *
     * @param string $resource The resource being consumed
     * @param int $quantity The amount consumed (default: 1)
     *
     * @return SubscriptionUsage The created usage record
     */
    public function recordUsage(string $resource, int $quantity = 1): SubscriptionUsage
    {
        return $this->usages()->create([
            'resource' => $resource,
            'quantity' => $quantity,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ]);
    }

    /**
     * Get the total usage of a resource for the current period.
     *
     * Sums all usage records for the specified resource within
     * the current billing period.
     *
     * @param string $resource The resource identifier
     *
     * @return int The total usage quantity
     */
    public function getUsage(string $resource): int
    {
        return $this->usages()
            ->where('resource', $resource)
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->sum('quantity');
    }

    /**
     * Query scope to filter only active subscriptions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query
     */
    public function scopeActive($query) { return $query->where('status', 'active'); }

    /**
     * Query scope to filter only trial subscriptions.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query The query builder
     *
     * @return \Illuminate\Database\Eloquent\Builder The modified query
     */
    public function scopeOnTrial($query) { return $query->where('status', 'trial'); }
}