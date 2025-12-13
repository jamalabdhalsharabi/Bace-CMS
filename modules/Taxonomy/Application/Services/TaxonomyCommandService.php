<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Application\Services;

use Modules\Taxonomy\Application\Actions\CreateTaxonomyAction;
use Modules\Taxonomy\Application\Actions\DeleteTaxonomyAction;
use Modules\Taxonomy\Application\Actions\MoveTaxonomyAction;
use Modules\Taxonomy\Application\Actions\ReorderTaxonomyAction;
use Modules\Taxonomy\Application\Actions\UpdateTaxonomyAction;
use Modules\Taxonomy\Domain\DTO\TaxonomyData;
use Modules\Taxonomy\Domain\Models\Taxonomy;

/**
 * Taxonomy Command Service.
 */
final class TaxonomyCommandService
{
    public function __construct(
        private readonly CreateTaxonomyAction $createAction,
        private readonly UpdateTaxonomyAction $updateAction,
        private readonly DeleteTaxonomyAction $deleteAction,
        private readonly ReorderTaxonomyAction $reorderAction,
        private readonly MoveTaxonomyAction $moveAction,
    ) {}

    public function create(TaxonomyData $data): Taxonomy
    {
        return $this->createAction->execute($data);
    }

    public function update(Taxonomy $taxonomy, TaxonomyData $data): Taxonomy
    {
        return $this->updateAction->execute($taxonomy, $data);
    }

    public function delete(Taxonomy $taxonomy): bool
    {
        return $this->deleteAction->execute($taxonomy);
    }

    public function reorder(array $order): void
    {
        $this->reorderAction->execute($order);
    }

    public function move(Taxonomy $taxonomy, ?string $parentId): Taxonomy
    {
        return $this->moveAction->execute($taxonomy, $parentId);
    }
}
