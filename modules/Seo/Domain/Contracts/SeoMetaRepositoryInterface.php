<?php

declare(strict_types=1);

namespace Modules\Seo\Domain\Contracts;

use Modules\Core\Domain\Contracts\RepositoryInterface;
use Modules\Seo\Domain\Models\SeoMeta;

/**
 * SEO Meta Repository Interface.
 *
 * Read-only interface for SeoMeta queries.
 * Write operations should be performed through Action classes.
 *
 * @extends RepositoryInterface<SeoMeta>
 *
 * @package Modules\Seo\Domain\Contracts
 * @author  CMS Development Team
 * @since   1.0.0
 */
interface SeoMetaRepositoryInterface extends RepositoryInterface
{
    /**
     * Find SEO meta by entity type and ID.
     *
     * @param string $entityType The entity type
     * @param string $entityId The entity ID
     *
     * @return SeoMeta|null
     */
    public function findByEntity(string $entityType, string $entityId): ?SeoMeta;
}
