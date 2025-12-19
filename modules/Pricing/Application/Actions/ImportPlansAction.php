<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Pricing\Domain\Models\PricingPlan;
use Modules\Pricing\Domain\Repositories\PlanRepository;

/**
 * Import Plans Action.
 *
 * Imports pricing plans from external data.
 * Supports merge (skip existing) and replace modes.
 *
 * @package Modules\Pricing\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ImportPlansAction extends Action
{
    /**
     * @param PlanRepository $repository Read-only repository for checking existing plans
     */
    public function __construct(
        private readonly PlanRepository $repository
    ) {}

    /**
     * Execute the import action.
     *
     * @param array<int, array<string, mixed>> $data Array of plan data to import
     * @param string $mode Import mode: 'merge' (skip existing) or 'replace' (update existing)
     *
     * @return array{imported: int}
     */
    public function execute(array $data, string $mode = 'merge'): array
    {
        $imported = 0;
        
        foreach ($data as $planData) {
            $existing = $this->repository->findBySlug($planData['slug']);
            
            if ($mode === 'replace') {
                if ($existing) {
                    $existing->update($planData);
                } else {
                    PricingPlan::create($planData);
                }
                $imported++;
            } else {
                // Merge mode - only create if not exists
                if (!$existing) {
                    PricingPlan::create($planData);
                    $imported++;
                }
            }
        }
        
        return ['imported' => $imported];
    }
}
