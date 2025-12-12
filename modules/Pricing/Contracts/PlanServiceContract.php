<?php

declare(strict_types=1);

namespace Modules\Pricing\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Pricing\Domain\Models\PricingPlan;

interface PlanServiceContract
{
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator;
    public function getActive(): \Illuminate\Database\Eloquent\Collection;
    public function find(string $id): ?PricingPlan;
    public function findBySlug(string $slug): ?PricingPlan;
    public function create(array $data): PricingPlan;
    public function update(PricingPlan $plan, array $data): PricingPlan;
    public function delete(PricingPlan $plan): bool;
    public function activate(PricingPlan $plan): PricingPlan;
    public function deactivate(PricingPlan $plan): PricingPlan;
    public function setAsDefault(PricingPlan $plan): PricingPlan;
    public function setAsRecommended(PricingPlan $plan): PricingPlan;
    public function compare(array $planIds): array;
    public function clone(PricingPlan $plan, string $newSlug): PricingPlan;
    public function reorder(array $order): bool;
    public function getAnalytics(PricingPlan $plan): array;
    public function export(array $options = []): array;
    public function import(array $data, string $mode = 'merge'): array;
    public function link(PricingPlan $plan, string $entityType, string $entityId, bool $isRequired = false): \Modules\Pricing\Domain\Models\PlanLink;
    public function unlink(PricingPlan $plan, string $entityType, string $entityId): bool;
    public function getLinks(PricingPlan $plan): \Illuminate\Database\Eloquent\Collection;
}