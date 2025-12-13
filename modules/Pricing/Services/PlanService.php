<?php

declare(strict_types=1);

namespace Modules\Pricing\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Pricing\Contracts\PlanServiceContract;
use Modules\Pricing\Domain\Models\PricingPlan;

/**
 * Class PlanService
 *
 * Service class for managing pricing plans including CRUD,
 * activation, comparison, cloning, analytics, and linking.
 *
 * @package Modules\Pricing\Services
 */
class PlanService implements PlanServiceContract
{
    /**
     * Get a paginated list of pricing plans.
     *
     * @param array $filters Optional filters: 'status', 'type'
     * @param int $perPage Results per page
     *
     * @return LengthAwarePaginator Paginated plans
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = PricingPlan::with(['translation', 'prices', 'features.translation']);
        
        if (!empty($filters['status'])) $query->where('status', $filters['status']);
        if (!empty($filters['type'])) $query->where('type', $filters['type']);
        
        return $query->ordered()->paginate($perPage);
    }

    /**
     * Get all active pricing plans.
     *
     * @return Collection Collection of active PricingPlan models
     */
    public function getActive(): Collection
    {
        return PricingPlan::active()
            ->with(['translation', 'prices.currency', 'features.translation', 'limits'])
            ->ordered()
            ->get();
    }

    /**
     * Find a pricing plan by its UUID.
     *
     * @param string $id The plan UUID
     *
     * @return PricingPlan|null The found plan or null
     */
    public function find(string $id): ?PricingPlan
    {
        return PricingPlan::with(['translations', 'prices.currency', 'features.translations', 'limits'])->find($id);
    }

    /**
     * Find a pricing plan by its slug.
     *
     * @param string $slug The plan slug
     *
     * @return PricingPlan|null The found plan or null
     */
    public function findBySlug(string $slug): ?PricingPlan
    {
        return PricingPlan::where('slug', $slug)
            ->with(['translations', 'prices.currency', 'features.translations', 'limits'])
            ->first();
    }

    /**
     * Create a new pricing plan with translations, prices, features and limits.
     *
     * @param array $data Plan data
     *
     * @return PricingPlan The created plan
     *
     * @throws \Throwable If transaction fails
     */
    public function create(array $data): PricingPlan
    {
        return DB::transaction(function () use ($data) {
            $plan = PricingPlan::create([
                'slug' => $data['slug'],
                'type' => $data['type'] ?? 'subscription',
                'trial_days' => $data['trial_days'] ?? 0,
                'status' => 'draft',
                'billing_periods' => $data['billing_periods'] ?? ['monthly'],
                'sort_order' => $data['sort_order'] ?? 0,
                'created_by' => auth()->id(),
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $plan->translations()->create(['locale' => $locale, ...$trans]);
                }
            }

            if (!empty($data['prices'])) {
                foreach ($data['prices'] as $period => $currencies) {
                    foreach ($currencies as $price) {
                        $plan->prices()->create([
                            'billing_period' => $period,
                            'currency_id' => $price['currency_id'],
                            'amount' => $price['amount'],
                            'compare_amount' => $price['compare_amount'] ?? null,
                        ]);
                    }
                }
            }

            if (!empty($data['features'])) {
                foreach ($data['features'] as $i => $feature) {
                    $f = $plan->features()->create([
                        'feature_key' => $feature['key'],
                        'value' => $feature['value'],
                        'type' => $feature['type'] ?? 'boolean',
                        'is_highlighted' => $feature['is_highlighted'] ?? false,
                        'sort_order' => $i,
                    ]);
                    if (!empty($feature['translations'])) {
                        foreach ($feature['translations'] as $locale => $trans) {
                            $f->translations()->create(['locale' => $locale, ...$trans]);
                        }
                    }
                }
            }

            if (!empty($data['limits'])) {
                foreach ($data['limits'] as $resource => $limit) {
                    $plan->limits()->create([
                        'resource' => $resource,
                        'limit_value' => $limit['limit'],
                        'period' => $limit['period'] ?? null,
                    ]);
                }
            }

            return $plan->fresh(['translations', 'prices', 'features', 'limits']);
        });
    }

    /**
     * Update an existing pricing plan.
     *
     * @param PricingPlan $plan The plan to update
     * @param array $data Updated data
     *
     * @return PricingPlan The updated plan
     *
     * @throws \Throwable If transaction fails
     */
    public function update(PricingPlan $plan, array $data): PricingPlan
    {
        return DB::transaction(function () use ($plan, $data) {
            $plan->update(array_filter($data, fn($v) => $v !== null));

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $plan->translations()->updateOrCreate(['locale' => $locale], $trans);
                }
            }

            return $plan->fresh(['translations', 'prices', 'features', 'limits']);
        });
    }

    /**
     * Delete a pricing plan.
     *
     * @param PricingPlan $plan The plan to delete
     *
     * @return bool True if successful
     *
     * @throws \Exception If plan has active subscriptions
     */
    public function delete(PricingPlan $plan): bool
    {
        if ($plan->subscriptions()->active()->exists()) {
            throw new \Exception('Cannot delete plan with active subscriptions');
        }
        return $plan->delete();
    }

    /**
     * Activate a pricing plan.
     *
     * @param PricingPlan $plan The plan to activate
     *
     * @return PricingPlan The activated plan
     */
    public function activate(PricingPlan $plan): PricingPlan
    {
        $plan->update(['status' => 'active']);
        return $plan->fresh();
    }

    /**
     * Deactivate a pricing plan.
     *
     * @param PricingPlan $plan The plan to deactivate
     *
     * @return PricingPlan The deactivated plan
     */
    public function deactivate(PricingPlan $plan): PricingPlan
    {
        $plan->update(['status' => 'inactive']);
        return $plan->fresh();
    }

    /**
     * Set a plan as the default.
     *
     * @param PricingPlan $plan The plan to set as default
     *
     * @return PricingPlan The updated plan
     */
    public function setAsDefault(PricingPlan $plan): PricingPlan
    {
        DB::transaction(function () use ($plan) {
            PricingPlan::where('is_default', true)->update(['is_default' => false]);
            $plan->update(['is_default' => true]);
        });
        return $plan->fresh();
    }

    /**
     * Set a plan as the recommended option.
     *
     * @param PricingPlan $plan The plan to recommend
     *
     * @return PricingPlan The updated plan
     */
    public function setAsRecommended(PricingPlan $plan): PricingPlan
    {
        DB::transaction(function () use ($plan) {
            PricingPlan::where('is_recommended', true)->update(['is_recommended' => false]);
            $plan->update(['is_recommended' => true]);
        });
        return $plan->fresh();
    }

    /**
     * Compare multiple plans side by side.
     *
     * @param array $planIds Array of plan UUIDs to compare
     *
     * @return array Comparison data with plans and features matrix
     */
    public function compare(array $planIds): array
    {
        $plans = PricingPlan::whereIn('id', $planIds)
            ->with(['translation', 'prices', 'features.translation'])
            ->ordered()
            ->get();

        $allFeatureKeys = $plans->flatMap(fn($p) => $p->features->pluck('feature_key'))->unique();

        $matrix = [];
        foreach ($allFeatureKeys as $key) {
            $matrix[$key] = [];
            foreach ($plans as $plan) {
                $feature = $plan->features->firstWhere('feature_key', $key);
                $matrix[$key][$plan->slug] = $feature?->value ?? '-';
            }
        }

        return ['plans' => $plans, 'features_matrix' => $matrix];
    }

    /**
     * Clone a plan with a new slug.
     *
     * @param PricingPlan $plan The plan to clone
     * @param string $newSlug The slug for the new plan
     *
     * @return PricingPlan The cloned plan
     */
    public function clone(PricingPlan $plan, string $newSlug): PricingPlan
    {
        return DB::transaction(function () use ($plan, $newSlug) {
            $newPlan = $plan->replicate(['id', 'slug', 'status', 'is_default', 'is_recommended']);
            $newPlan->slug = $newSlug;
            $newPlan->status = 'draft';
            $newPlan->is_default = false;
            $newPlan->is_recommended = false;
            $newPlan->save();

            foreach ($plan->translations as $trans) {
                $newPlan->translations()->create($trans->only(['locale', 'name', 'description', 'short_description', 'cta_text', 'badge_text']));
            }

            foreach ($plan->prices as $price) {
                $newPlan->prices()->create($price->only(['currency_id', 'billing_period', 'amount', 'compare_amount', 'setup_fee']));
            }

            foreach ($plan->features as $feature) {
                $newFeature = $newPlan->features()->create($feature->only(['feature_key', 'value', 'type', 'is_highlighted', 'sort_order']));
                foreach ($feature->translations as $trans) {
                    $newFeature->translations()->create($trans->only(['locale', 'label', 'tooltip']));
                }
            }

            foreach ($plan->limits as $limit) {
                $newPlan->limits()->create($limit->only(['resource', 'limit_value', 'period']));
            }

            return $newPlan->fresh(['translations', 'prices', 'features', 'limits']);
        });
    }

    /**
     * Reorder plans by their IDs.
     *
     * @param array $order Array of plan UUIDs in order
     *
     * @return bool True if successful
     */
    public function reorder(array $order): bool
    {
        return DB::transaction(function () use ($order) {
            foreach ($order as $index => $id) {
                PricingPlan::where('id', $id)->update(['sort_order' => $index]);
            }
            return true;
        });
    }

    /**
     * Get analytics for a plan.
     *
     * @param PricingPlan $plan The plan
     *
     * @return array Analytics data including subscriber counts and MRR
     */
    public function getAnalytics(PricingPlan $plan): array
    {
        $subscriptions = $plan->subscriptions();
        $activeCount = $subscriptions->active()->count();
        $totalCount = $subscriptions->count();
        
        $churnedLast30 = $subscriptions
            ->where('status', 'cancelled')
            ->where('cancelled_at', '>=', now()->subDays(30))
            ->count();

        $prices = $plan->prices->where('billing_period', 'monthly');
        $avgPrice = $prices->avg('amount') ?? 0;
        $mrr = $activeCount * $avgPrice;

        return [
            'plan_id' => $plan->id,
            'stats' => [
                'total_subscribers' => $totalCount,
                'active_subscribers' => $activeCount,
                'churned_last_30_days' => $churnedLast30,
                'mrr' => round($mrr, 2),
                'arr' => round($mrr * 12, 2),
            ],
        ];
    }

    /**
     * Export plans to array format.
     *
     * @param array $options Export options
     *
     * @return array Exported plan data
     */
    public function export(array $options = []): array
    {
        $query = PricingPlan::with(['translations', 'prices', 'features.translations', 'limits']);
        
        if (!empty($options['status'])) {
            $query->where('status', $options['status']);
        }

        return $query->get()->map(fn($plan) => [
            'slug' => $plan->slug,
            'type' => $plan->type,
            'trial_days' => $plan->trial_days,
            'billing_periods' => $plan->billing_periods,
            'translations' => $plan->translations->keyBy('locale')->map->only(['name', 'description', 'short_description']),
            'prices' => $plan->prices->groupBy('billing_period')->map(fn($p) => $p->map->only(['currency_id', 'amount', 'compare_amount'])),
            'features' => $plan->features->map(fn($f) => [
                'key' => $f->feature_key,
                'value' => $f->value,
                'type' => $f->type,
                'translations' => $f->translations->keyBy('locale')->map->only(['label', 'tooltip']),
            ]),
            'limits' => $plan->limits->keyBy('resource')->map->only(['limit_value', 'period']),
        ])->toArray();
    }

    /**
     * Import plans from array data.
     *
     * @param array $data Plan data to import
     * @param string $mode Import mode: 'merge' or 'replace'
     *
     * @return array Import results
     */
    public function import(array $data, string $mode = 'merge'): array
    {
        $results = ['created' => 0, 'updated' => 0, 'errors' => []];

        foreach ($data as $planData) {
            try {
                $existing = $this->findBySlug($planData['slug']);
                
                if ($existing && $mode === 'merge') {
                    $this->update($existing, $planData);
                    $results['updated']++;
                } elseif (!$existing) {
                    $this->create($planData);
                    $results['created']++;
                }
            } catch (\Exception $e) {
                $results['errors'][] = ['slug' => $planData['slug'] ?? 'unknown', 'error' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Link a plan to an entity.
     *
     * @param PricingPlan $plan The plan
     * @param string $entityType Entity type (product, service, event, project)
     * @param string $entityId Entity UUID
     * @param bool $isRequired Whether the plan is required
     *
     * @return \Modules\Pricing\Domain\Models\PlanLink The created link
     */
    public function link(PricingPlan $plan, string $entityType, string $entityId, bool $isRequired = false): \Modules\Pricing\Domain\Models\PlanLink
    {
        $linkableType = $this->resolveLinkableType($entityType);
        
        return $plan->links()->updateOrCreate(
            ['linkable_type' => $linkableType, 'linkable_id' => $entityId],
            ['is_required' => $isRequired]
        );
    }

    /**
     * Remove a link from a plan.
     *
     * @param PricingPlan $plan The plan
     * @param string $entityType Entity type
     * @param string $entityId Entity UUID
     *
     * @return bool True if successful
     */
    public function unlink(PricingPlan $plan, string $entityType, string $entityId): bool
    {
        $linkableType = $this->resolveLinkableType($entityType);
        
        return $plan->links()
            ->where('linkable_type', $linkableType)
            ->where('linkable_id', $entityId)
            ->delete() > 0;
    }

    /**
     * Get all links for a plan.
     *
     * @param PricingPlan $plan The plan
     *
     * @return \Illuminate\Database\Eloquent\Collection Plan links
     */
    public function getLinks(PricingPlan $plan): \Illuminate\Database\Eloquent\Collection
    {
        return $plan->links()->with('linkable')->get();
    }

    /**
     * Resolve entity type to fully qualified class name.
     *
     * @param string $entityType Short entity type
     *
     * @return string Fully qualified class name
     */
    protected function resolveLinkableType(string $entityType): string
    {
        return match ($entityType) {
            'product' => 'Modules\\Products\\Domain\\Models\\Product',
            'service' => 'Modules\\Services\\Domain\\Models\\Service',
            'event' => 'Modules\\Events\\Domain\\Models\\Event',
            'project' => 'Modules\\Projects\\Domain\\Models\\Project',
            default => $entityType,
        };
    }
}
