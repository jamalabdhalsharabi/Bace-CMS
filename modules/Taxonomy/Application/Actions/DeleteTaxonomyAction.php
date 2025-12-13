<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Taxonomy\Domain\Models\Taxonomy;
use Modules\Taxonomy\Domain\Repositories\TaxonomyRepository;

final class DeleteTaxonomyAction extends Action
{
    public function __construct(
        private readonly TaxonomyRepository $repository
    ) {}

    public function execute(Taxonomy $taxonomy): bool
    {
        Taxonomy::where('parent_id', $taxonomy->id)
            ->update(['parent_id' => $taxonomy->parent_id]);

        return $this->repository->delete($taxonomy->id);
    }
}
