<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Domain\Contracts;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Contracts\RepositoryInterface;
use Modules\Taxonomy\Domain\Models\Taxonomy;

/**
 * Taxonomy Repository Interface.
 *
 * Read-only interface for Taxonomy queries.
 * All write operations should be performed through Action classes.
 *
 * @extends RepositoryInterface<Taxonomy>
 *
 * @package Modules\Taxonomy\Domain\Contracts
 * @author  CMS Development Team
 * @since   1.0.0
 */
interface TaxonomyRepositoryInterface extends RepositoryInterface
{
    /**
     * Get taxonomies by type with optional parent filter.
     *
     * @param string $typeId The taxonomy type ID
     * @param string|null $parentId The parent taxonomy ID
     *
     * @return Collection<int, Taxonomy>
     */
    public function getByType(string $typeId, ?string $parentId = null): Collection;

    /**
     * Get taxonomy tree with children.
     *
     * @param string $typeId The taxonomy type ID
     *
     * @return Collection<int, Taxonomy>
     */
    public function getTree(string $typeId): Collection;

    /**
     * Find taxonomy by slug within a type.
     *
     * @param string $slug The taxonomy slug
     * @param string $typeId The taxonomy type ID
     *
     * @return Taxonomy|null
     */
    public function findBySlug(string $slug, string $typeId): ?Taxonomy;
}
