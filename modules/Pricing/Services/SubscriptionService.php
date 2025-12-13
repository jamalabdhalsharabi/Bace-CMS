<?php

declare(strict_types=1);

namespace Modules\Pricing\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Pricing\Contracts\SubscriptionServiceContract;
use Modules\Pricing\Domain\Models\Coupon;
use Modules\Pricing\Domain\Models\PricingPlan;
use Modules\Pricing\Domain\Models\Subscription;

/**
 * Class SubscriptionService
 *
 * Service class for managing subscriptions including
 * creation, upgrades, downgrades, cancellation, and renewal.
 *
 * @package Modules\Pricing\Services
 */
class SubscriptionService implements SubscriptionServiceContract
{
    /**
     * Create a new subscription for a user.
     *
     * @param string $userId The user UUID
     * @param string $planId The plan UUID
     * @param array $data Subscription data including billing_period, payment_method, coupon_code
     *
     * @return Subscription The created subscription
     *
     * @throws \Throwable If transaction fails
     */
    public function create(string $userId, string $planId, array $data): Subscription
    {
        $plan = PricingPlan::findOrFail($planId);

        return DB::transaction(function () use ($userId, $plan, $data) {
            $trialDays = $plan->trial_days;
            $startsAt = now();
            $endsAt = $this->calculateEndDate($startsAt, $data['billing_period'] ?? 'monthly');
            
            $subscription = Subscription::create([
                'user_id' => $userId,
                'plan_id' => $plan->id,
                'billing_period' => $data['billing_period'] ?? 'monthly',
                'status' => $trialDays > 0 ? 'trial' : 'active',
                'trial_ends_at' => $trialDays > 0 ? now()->addDays($trialDays) : null,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'payment_method' => $data['payment_method'] ?? null,
            ]);

            if (!empty($data['coupon_code'])) {
                $coupon = Coupon::findByCode($data['coupon_code']);
                if ($coupon && $coupon->canBeUsedBy($userId) && $coupon->appliesToPlan($plan->id)) {
                    $coupon->recordUsage($userId, $subscription->id);
                }
            }

            return $subscription;
        });
    }

    /**
     * Find a subscription by its UUID.
     *
     * @param string $id The subscription UUID
     *
     * @return Subscription|null The found subscription or null
     */
    public function find(string $id): ?Subscription
    {
        return Subscription::with(['plan.translation', 'usages'])->find($id);
    }

    /**
     * Get all subscriptions for a user.
     *
     * @param string $userId The user UUID
     *
     * @return Collection User's subscriptions
     */
    public function getForUser(string $userId): Collection
    {
        return Subscription::where('user_id', $userId)
            ->with(['plan.translation'])
            ->latest()
            ->get();
    }

    /**
     * Upgrade a subscription to a higher plan.
     *
     * @param Subscription $subscription The subscription to upgrade
     * @param string $newPlanId The new plan UUID
     * @param bool $prorate Whether to prorate the upgrade
     *
     * @return Subscription The upgraded subscription
     */
    public function upgrade(Subscription $subscription, string $newPlanId, bool $prorate = true): Subscription
    {
        return DB::transaction(function () use ($subscription, $newPlanId) {
            $subscription->update([
                'plan_id' => $newPlanId,
                'ends_at' => $this->calculateEndDate(now(), $subscription->billing_period),
            ]);
            return $subscription->fresh(['plan']);
        });
    }

    /**
     * Schedule a downgrade to a lower plan at period end.
     *
     * @param Subscription $subscription The subscription to downgrade
     * @param string $newPlanId The new plan UUID
     *
     * @return Subscription The subscription with pending downgrade
     */
    public function downgrade(Subscription $subscription, string $newPlanId): Subscription
    {
        $subscription->update(['pending_plan_id' => $newPlanId]);
        return $subscription->fresh();
    }

    /**
     * Cancel a subscription.
     *
     * @param Subscription $subscription The subscription to cancel
     * @param string|null $reason Cancellation reason
     *
     * @return Subscription The cancelled subscription
     */
    public function cancel(Subscription $subscription, ?string $reason = null): Subscription
    {
        return $subscription->cancel($reason);
    }

    /**
     * Resume a cancelled or paused subscription.
     *
     * @param Subscription $subscription The subscription to resume
     *
     * @return Subscription The resumed subscription
     */
    public function resume(Subscription $subscription): Subscription
    {
        return $subscription->resume();
    }

    /**
     * Renew a subscription for another period.
     *
     * @param Subscription $subscription The subscription to renew
     *
     * @return Subscription The renewed subscription
     */
    public function renew(Subscription $subscription): Subscription
    {
        return DB::transaction(function () use ($subscription) {
            if ($subscription->pending_plan_id) {
                $subscription->plan_id = $subscription->pending_plan_id;
                $subscription->pending_plan_id = null;
            }

            $subscription->starts_at = now();
            $subscription->ends_at = $this->calculateEndDate(now(), $subscription->billing_period);
            $subscription->status = 'active';
            $subscription->save();

            return $subscription->fresh(['plan']);
        });
    }

    /**
     * Pause a subscription temporarily.
     *
     * @param Subscription $subscription The subscription to pause
     * @param string|null $resumeAt Optional date to auto-resume
     *
     * @return Subscription The paused subscription
     */
    public function pause(Subscription $subscription, ?string $resumeAt = null): Subscription
    {
        return $subscription->pause($resumeAt);
    }

    /**
     * Process a refund for a subscription.
     *
     * @param Subscription $subscription The subscription to refund
     * @param string $type Refund type: 'full', 'prorated', 'partial'
     * @param float|null $amount Amount for partial refunds
     *
     * @return array Refund result with success status and amount
     */
    public function refund(Subscription $subscription, string $type = 'full', ?float $amount = null): array
    {
        $lastPaymentAmount = 100.00; // Placeholder - would come from payment system
        
        $refundAmount = match ($type) {
            'full' => $lastPaymentAmount,
            'prorated' => $this->calculateProratedRefund($subscription, $lastPaymentAmount),
            'partial' => $amount ?? 0,
            default => 0,
        };

        return DB::transaction(function () use ($subscription, $refundAmount, $type) {
            $subscription->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancel_reason' => 'refund_' . $type,
            ]);

            return [
                'success' => true,
                'refund_amount' => $refundAmount,
                'type' => $type,
                'subscription_id' => $subscription->id,
            ];
        });
    }

    /**
     * Extend a subscription by additional days.
     *
     * @param Subscription $subscription The subscription to extend
     * @param int $days Number of days to add
     * @param string|null $reason Extension reason
     *
     * @return Subscription The extended subscription
     */
    public function extend(Subscription $subscription, int $days, ?string $reason = null): Subscription
    {
        return DB::transaction(function () use ($subscription, $days, $reason) {
            $subscription->extend($days);
            
            $meta = $subscription->meta ?? [];
            $meta['extensions'][] = [
                'days' => $days,
                'reason' => $reason,
                'extended_at' => now()->toISOString(),
            ];
            $subscription->update(['meta' => $meta]);
            
            return $subscription->fresh();
        });
    }

    /**
     * Calculate prorated refund amount.
     *
     * @param Subscription $subscription The subscription
     * @param float $totalAmount Total payment amount
     *
     * @return float Prorated refund amount
     */
    protected function calculateProratedRefund(Subscription $subscription, float $totalAmount): float
    {
        $totalDays = $subscription->starts_at->diffInDays($subscription->ends_at);
        $usedDays = $subscription->starts_at->diffInDays(now());
        $remainingDays = max(0, $totalDays - $usedDays);
        
        return round(($remainingDays / $totalDays) * $totalAmount, 2);
    }

    /**
     * Calculate subscription end date based on period.
     *
     * @param mixed $startDate Start date
     * @param string $period Billing period
     *
     * @return \Carbon\Carbon Calculated end date
     */
    protected function calculateEndDate($startDate, string $period): \Carbon\Carbon
    {
        return match ($period) {
            'monthly' => $startDate->copy()->addMonth(),
            'quarterly' => $startDate->copy()->addMonths(3),
            'yearly' => $startDate->copy()->addYear(),
            'lifetime' => $startDate->copy()->addYears(100),
            default => $startDate->copy()->addMonth(),
        };
    }
}
