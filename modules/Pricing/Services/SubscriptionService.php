<?php

declare(strict_types=1);

namespace Modules\Pricing\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Pricing\Contracts\SubscriptionServiceContract;
use Modules\Pricing\Domain\Models\Coupon;
use Modules\Pricing\Domain\Models\PricingPlan;
use Modules\Pricing\Domain\Models\Subscription;

class SubscriptionService implements SubscriptionServiceContract
{
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

    public function find(string $id): ?Subscription
    {
        return Subscription::with(['plan.translation', 'usages'])->find($id);
    }

    public function getForUser(string $userId): Collection
    {
        return Subscription::where('user_id', $userId)
            ->with(['plan.translation'])
            ->latest()
            ->get();
    }

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

    public function downgrade(Subscription $subscription, string $newPlanId): Subscription
    {
        $subscription->update(['pending_plan_id' => $newPlanId]);
        return $subscription->fresh();
    }

    public function cancel(Subscription $subscription, ?string $reason = null): Subscription
    {
        return $subscription->cancel($reason);
    }

    public function resume(Subscription $subscription): Subscription
    {
        return $subscription->resume();
    }

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

    public function pause(Subscription $subscription, ?string $resumeAt = null): Subscription
    {
        return $subscription->pause($resumeAt);
    }

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

    protected function calculateProratedRefund(Subscription $subscription, float $totalAmount): float
    {
        $totalDays = $subscription->starts_at->diffInDays($subscription->ends_at);
        $usedDays = $subscription->starts_at->diffInDays(now());
        $remainingDays = max(0, $totalDays - $usedDays);
        
        return round(($remainingDays / $totalDays) * $totalAmount, 2);
    }

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
