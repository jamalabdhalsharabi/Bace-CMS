<?php

declare(strict_types=1);

namespace Modules\Pricing\Contracts;

use Modules\Pricing\Domain\Models\Subscription;

interface SubscriptionServiceContract
{
    public function create(string $userId, string $planId, array $data): Subscription;
    public function find(string $id): ?Subscription;
    public function getForUser(string $userId): \Illuminate\Database\Eloquent\Collection;
    public function upgrade(Subscription $subscription, string $newPlanId, bool $prorate = true): Subscription;
    public function downgrade(Subscription $subscription, string $newPlanId): Subscription;
    public function cancel(Subscription $subscription, ?string $reason = null): Subscription;
    public function pause(Subscription $subscription, ?string $resumeAt = null): Subscription;
    public function resume(Subscription $subscription): Subscription;
    public function renew(Subscription $subscription): Subscription;
    public function refund(Subscription $subscription, string $type = 'full', ?float $amount = null): array;
    public function extend(Subscription $subscription, int $days, ?string $reason = null): Subscription;
}