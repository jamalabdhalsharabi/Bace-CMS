<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'));
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'plan_id');
    }

    public function pendingPlan(): BelongsTo
    {
        return $this->belongsTo(PricingPlan::class, 'pending_plan_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(SubscriptionUsage::class);
    }

    public function isActive(): bool { return $this->status === 'active'; }
    public function isOnTrial(): bool { return $this->status === 'trial' && $this->trial_ends_at > now(); }
    public function isPaused(): bool { return $this->status === 'paused'; }
    public function isCancelled(): bool { return $this->status === 'cancelled'; }
    public function isExpired(): bool { return $this->status === 'expired' || ($this->ends_at && $this->ends_at < now()); }

    public function cancel(?string $reason = null): self
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancel_reason' => $reason,
        ]);
        return $this;
    }

    public function pause(?string $resumeAt = null): self
    {
        $this->update([
            'status' => 'paused',
            'paused_at' => now(),
            'resume_at' => $resumeAt ? \Carbon\Carbon::parse($resumeAt) : null,
        ]);
        return $this;
    }

    public function resume(): self
    {
        $this->update(['status' => 'active', 'paused_at' => null, 'resume_at' => null]);
        return $this;
    }

    public function extend(int $days): self
    {
        $this->update(['ends_at' => $this->ends_at->addDays($days)]);
        return $this;
    }

    public function hasFeature(string $key): bool
    {
        return $this->plan->hasFeature($key);
    }

    public function getLimit(string $resource): ?int
    {
        return $this->plan->getLimit($resource);
    }

    public function recordUsage(string $resource, int $quantity = 1): SubscriptionUsage
    {
        return $this->usages()->create([
            'resource' => $resource,
            'quantity' => $quantity,
            'period_start' => now()->startOfMonth(),
            'period_end' => now()->endOfMonth(),
        ]);
    }

    public function getUsage(string $resource): int
    {
        return $this->usages()
            ->where('resource', $resource)
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->sum('quantity');
    }

    public function scopeActive($query) { return $query->where('status', 'active'); }
    public function scopeOnTrial($query) { return $query->where('status', 'trial'); }
}