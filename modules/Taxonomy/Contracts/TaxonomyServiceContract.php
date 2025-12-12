<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Taxonomy\Domain\Models\Taxonomy;
use Modules\Taxonomy\Domain\Models\TaxonomyType;

interface TaxonomyServiceContract
{
    public function getTypes(): Collection;

    public function getType(string $slug): ?TaxonomyType;

    public function getTaxonomies(string $typeSlug, ?string $parentId = null): Collection;

    public function getTree(string $typeSlug): Collection;

    public function find(string $id): ?Taxonomy;

    public function findBySlug(string $slug, ?string $typeSlug = null): ?Taxonomy;

    public function create(array $data): Taxonomy;

    public function update(Taxonomy $taxonomy, array $data): Taxonomy;

    public function delete(Taxonomy $taxonomy): bool;

    public function reorder(array $order): void;
}
