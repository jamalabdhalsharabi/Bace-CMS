<?php

declare(strict_types=1);

namespace Modules\Seo\Domain\Repositories;

use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Seo\Domain\Contracts\SeoMetaRepositoryInterface;
use Modules\Seo\Domain\Models\SeoMeta;

/**
 * SEO Meta Repository Implementation.
 *
 * Read-only repository for SeoMeta model queries.
 * Write operations should be performed through Action classes.
 *
 * @extends BaseRepository<SeoMeta>
 * @implements SeoMetaRepositoryInterface
 *
 * @package Modules\Seo\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class SeoMetaRepository extends BaseRepository implements SeoMetaRepositoryInterface
{
    /**
     * Create a new SeoMetaRepository instance.
     *
     * @param SeoMeta $model The SeoMeta model instance
     */
    public function __construct(SeoMeta $model)
    {
        parent::__construct($model);
    }

    /**
     * Find SEO meta by entity type and ID.
     *
     * @param string $entityType The entity type
     * @param string $entityId The entity ID
     *
     * @return SeoMeta|null
     */
    public function findByEntity(string $entityType, string $entityId): ?SeoMeta
    {
        return $this->query()
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->first();
    }
}
