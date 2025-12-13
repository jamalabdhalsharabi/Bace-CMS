<?php

declare(strict_types=1);

namespace Modules\Seo\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Seo\Domain\Models\SeoMeta;
use Modules\Seo\Domain\Repositories\SeoMetaRepository;

final class UpdateSeoMetaAction extends Action
{
    public function __construct(
        private readonly SeoMetaRepository $repository
    ) {}

    public function execute(string $entityType, string $entityId, array $data): SeoMeta
    {
        $meta = $this->repository->findByEntity($entityType, $entityId);

        if ($meta) {
            $this->repository->update($meta->id, $data);
            return $meta->fresh();
        }

        return SeoMeta::create(array_merge($data, [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]));
    }
}
