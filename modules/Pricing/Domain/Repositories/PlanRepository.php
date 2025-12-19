<?php

declare(strict_types=1);

namespace Modules\Pricing\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Pricing\Domain\Contracts\PlanRepositoryInterface;
use Modules\Pricing\Domain\Models\PricingPlan;

/**
 * Plan Repository Implementation.
 *
 * Read-only repository for PricingPlan model queries.
 * All write operations (create, update, delete) must be performed
 * through Action classes, not through this repository.
 *
 * @extends BaseRepository<PricingPlan>
 * @implements PlanRepositoryInterface
 *
 * @package Modules\Pricing\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PlanRepository extends BaseRepository implements PlanRepositoryInterface
{
    /**
     * Create a new PlanRepository instance.
     *
     * @param PricingPlan $model The PricingPlan model instance
     */
    public function __construct(PricingPlan $model)
    {
        parent::__construct($model);
    }

    /**
     * Get all active pricing plans.
     *
     * Returns plans ordered by sort_order for proper display sequence.
     *
     * @return Collection<int, PricingPlan>
     */
    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Get featured pricing plans.
     *
     * Returns only active plans that are also marked as featured.
     *
     * @return Collection<int, PricingPlan>
     */
    public function getFeatured(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->where('is_featured', true)
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * Find a plan by its slug.
     *
     * @param string $slug The plan slug
     *
     * @return PricingPlan|null
     */
    public function findBySlug(string $slug): ?PricingPlan
    {
        return $this->query()->where('slug', $slug)->first();
    }

    /**
     * Get paginated plans with optional filters.
     *
     * @param array<string, mixed> $filters Available filters: status, type
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<PricingPlan>
     */
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->when(isset($filters['type']), fn($q) => $q->where('type', $filters['type']))
            ->orderBy('sort_order')
            ->paginate($perPage);
    }

    /**
     * Get plans by multiple IDs.
     *
     * @param array<string> $ids Array of plan IDs
     *
     * @return Collection<int, PricingPlan>
     */
    public function getByIds(array $ids): Collection
    {
        return $this->query()->whereIn('id', $ids)->get();
    }

    /**
     * Get plans by multiple slugs.
     *
     * @param array<string> $slugs Array of plan slugs
     *
     * @return Collection<int, PricingPlan>
     */
    public function getBySlugs(array $slugs): Collection
    {
        return $this->query()->whereIn('slug', $slugs)->get();
    }

    /**
     * Get plan analytics data.
     *
     * Optimized to use minimal queries for analytics data.
     *
     * @param string $id The plan ID
     *
     * @return array{subscribers_count: int, active_subscribers: int, revenue: float}
     */
    public function getAnalytics(string $id): array
    {
        $plan = $this->find($id);
        
        if (!$plan) {
            return ['subscribers_count' => 0, 'active_subscribers' => 0, 'revenue' => 0.0];
        }

        // Single query with conditional aggregates
        $stats = $plan->subscriptions()
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active,
                SUM(amount) as revenue
            ")
            ->first();

        return [
            'subscribers_count' => (int) ($stats->total ?? 0),
            'active_subscribers' => (int) ($stats->active ?? 0),
            'revenue' => (float) ($stats->revenue ?? 0),
        ];
    }

    /**
     * Export all plans as array.
     *
     * @param array<string, mixed> $filters Optional filters
     *
     * @return array<int, array<string, mixed>>
     */
    public function exportAll(array $filters = []): array
    {
        return $this->query()
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->get()
            ->toArray();
    }

    /**
     * Get plan links.
     *
     * @param string $id The plan ID
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLinks(string $id): array
    {
        return DB::table('plan_links')
            ->where('plan_id', $id)
            ->get()
            ->toArray();
    }

    /**
     * Get the default plan.
     *
     * @return PricingPlan|null
     */
    public function getDefault(): ?PricingPlan
    {
        return $this->query()->where('is_default', true)->first();
    }

    /**
     * Get the recommended plan.
     *
     * @return PricingPlan|null
     */
    public function getRecommended(): ?PricingPlan
    {
        return $this->query()->where('is_recommended', true)->first();
    }
}
