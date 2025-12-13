<?php

declare(strict_types=1);

namespace Modules\Seo\Domain\Repositories;

use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Seo\Domain\Models\SeoMeta;

class SeoMetaRepository extends BaseRepository
{
    public function __construct(SeoMeta $model)
    {
        parent::__construct($model);
    }

    public function findByEntity(string $entityType, string $entityId): ?SeoMeta
    {
        return $this->model->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->first();
    }
}
