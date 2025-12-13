<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Taxonomy\Domain\Models\Taxonomy;
use Modules\Taxonomy\Domain\Repositories\TaxonomyRepository;

final class MoveTaxonomyAction extends Action
{
    public function __construct(
        private readonly TaxonomyRepository $repository
    ) {}

    public function execute(Taxonomy $taxonomy, ?string $parentId): Taxonomy
    {
        $this->repository->update($taxonomy->id, ['parent_id' => $parentId]);

        return $taxonomy->fresh();
    }
}
