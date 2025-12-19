<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Taxonomy\Domain\Contracts\TaxonomyRepositoryInterface;
use Modules\Taxonomy\Domain\Models\Taxonomy;

/**
 * Taxonomy Repository Implementation.
 *
 * Read-only repository for Taxonomy model queries.
 * All write operations (create, update, delete) must be performed
 * through Action classes, not through this repository.
 *
 * @extends BaseRepository<Taxonomy>
 * @implements TaxonomyRepositoryInterface
 *
 * @package Modules\Taxonomy\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class TaxonomyRepository extends BaseRepository implements TaxonomyRepositoryInterface
{
    /**
     * Create a new TaxonomyRepository instance.
     *
     * @param Taxonomy $model The Taxonomy model instance
     */
    public function __construct(Taxonomy $model)
    {
        parent::__construct($model);
    }

    /**
     * Get taxonomies by type with optional parent filter.
     *
     * @param string $typeId The taxonomy type ID
     * @param string|null $parentId The parent taxonomy ID (null for root)
     *
     * @return Collection<int, Taxonomy>
     */
    public function getByType(string $typeId, ?string $parentId = null): Collection
    {
        $query = $this->query()->where('type_id', $typeId);

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        return $query->ordered()->get();
    }

    /**
     * Get taxonomy tree with eager-loaded children.
     *
     * Uses recursive eager loading to prevent N+1 queries.
     *
     * @param string $typeId The taxonomy type ID
     *
     * @return Collection<int, Taxonomy>
     */
    public function getTree(string $typeId): Collection
    {
        return $this->query()
            ->where('type_id', $typeId)
            ->whereNull('parent_id')
            ->with(['children.children', 'translation'])
            ->ordered()
            ->get();
    }

    /**
     * Find taxonomy by slug within a type.
     *
     * @param string $slug The taxonomy slug
     * @param string $typeId The taxonomy type ID
     *
     * @return Taxonomy|null
     */
    public function findBySlug(string $slug, string $typeId): ?Taxonomy
    {
        return $this->query()
            ->where('type_id', $typeId)
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->first();
    }
}
