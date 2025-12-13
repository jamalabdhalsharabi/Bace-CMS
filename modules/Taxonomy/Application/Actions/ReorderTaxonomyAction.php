<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Taxonomy\Domain\Repositories\TaxonomyRepository;

final class ReorderTaxonomyAction extends Action
{
    public function __construct(
        private readonly TaxonomyRepository $repository
    ) {}

    public function execute(array $order): void
    {
        $this->repository->reorder($order);
    }
}
