<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Core\Application\Actions\Action;

final class UnlinkPlanAction extends Action
{
    public function execute(string $planId, string $entityType, string $entityId): bool
    {
        return DB::table('plan_links')
            ->where('plan_id', $planId)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->delete() > 0;
    }
}
