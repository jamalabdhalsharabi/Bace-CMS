<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Taxonomy\Domain\Models\Taxonomy;
use Modules\Taxonomy\Domain\Models\TaxonomyType;
use Modules\Taxonomy\Domain\Repositories\TaxonomyRepository;

/**
 * Taxonomy Query Service.
 */
final class TaxonomyQueryService
{
    public function __construct(
        private readonly TaxonomyRepository $repository
    ) {}

    public function getAllTypes(): Collection
    {
        return TaxonomyType::all();
    }

    public function getTypeBySlug(string $slug): ?TaxonomyType
    {
        return TaxonomyType::where('slug', $slug)->first();
    }

    public function getByType(string $typeId, ?string $parentId = null): Collection
    {
        return $this->repository
            ->with(['translation', 'children'])
            ->getByType($typeId, $parentId);
    }

    public function getTree(string $typeId): Collection
    {
        return $this->repository->getTree($typeId);
    }

    public function find(string $id): ?Taxonomy
    {
        return $this->repository
            ->with(['translations', 'parent', 'children'])
            ->find($id);
    }

    public function findBySlug(string $slug, string $typeId): ?Taxonomy
    {
        return $this->repository
            ->with(['translations'])
            ->findBySlug($slug, $typeId);
    }
}
