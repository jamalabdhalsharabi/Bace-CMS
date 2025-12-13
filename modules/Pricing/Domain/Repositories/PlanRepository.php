<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Pricing\Domain\Models\PricingPlan;

/**
 * Plan Repository.
 *
 * @extends BaseRepository<PricingPlan>
 */
final class PlanRepository extends BaseRepository
{
    public function __construct(PricingPlan $model)
    {
        parent::__construct($model);
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function getFeatured(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->get();
    }

    public function findBySlug(string $slug): ?Plan
    {
        return $this->query()->where('slug', $slug)->first();
    }
}
