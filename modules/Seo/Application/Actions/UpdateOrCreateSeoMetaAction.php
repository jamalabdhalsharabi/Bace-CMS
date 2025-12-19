<?php

declare(strict_types=1);

namespace Modules\Seo\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Seo\Domain\Models\SeoMeta;

/**
 * Update Or Create SEO Meta Action.
 *
 * Creates or updates SEO meta data for an entity.
 *
 * @package Modules\Seo\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UpdateOrCreateSeoMetaAction extends Action
{
    /**
     * Execute the update or create action.
     *
     * @param string $entityType The entity type
     * @param string $entityId The entity ID
     * @param array<string, mixed> $data The SEO meta data
     *
     * @return SeoMeta The created or updated SEO meta
     */
    public function execute(string $entityType, string $entityId, array $data): SeoMeta
    {
        return SeoMeta::updateOrCreate(
            ['entity_type' => $entityType, 'entity_id' => $entityId],
            $data
        );
    }
}
