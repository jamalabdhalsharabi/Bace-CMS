<?php

declare(strict_types=1);

namespace Modules\Pricing\Application\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Core\Application\Actions\Action;

final class LinkPlanAction extends Action
{
    public function execute(string $planId, string $entityType, string $entityId, bool $isRequired = false): object
    {
        $id = DB::table('plan_links')->insertGetId([
            'plan_id' => $planId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'is_required' => $isRequired,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        return (object) ['id' => $id];
    }
}
