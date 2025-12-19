<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Domain\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Contracts\RepositoryInterface;
use Modules\StaticBlocks\Domain\Models\StaticBlock;

/**
 * Static Block Repository Interface.
 *
 * Defines the contract for static block data access operations.
 * Extends the base RepositoryInterface with block-specific methods
 * for managing reusable content blocks.
 *
 * @extends RepositoryInterface<StaticBlock>
 *
 * @package Modules\StaticBlocks\Domain\Contracts
 * @author  CMS Development Team
 * @since   1.0.0
 */
interface StaticBlockRepositoryInterface extends RepositoryInterface
{
    /**
     * Get paginated static blocks with optional filters.
     *
     * @param array<string, mixed> $filters Filter criteria
     * @param int                  $perPage Items per page
     *
     * @return LengthAwarePaginator
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find a block by its unique identifier.
     *
     * @param string $identifier The block identifier
     *
     * @return StaticBlock|null
     */
    public function findByIdentifier(string $identifier): ?StaticBlock;

    /**
     * Get all active blocks.
     *
     * @return Collection<int, StaticBlock>
     */
    public function getActive(): Collection;

    /**
     * Get blocks by type.
     *
     * @param string $type Block type (html, text, widget, etc.)
     *
     * @return Collection<int, StaticBlock>
     */
    public function getByType(string $type): Collection;

    /**
     * Get trashed blocks.
     *
     * @param int $perPage Items per page
     *
     * @return LengthAwarePaginator
     */
    public function getTrashed(int $perPage = 15): LengthAwarePaginator;

    /**
     * Restore a soft-deleted block.
     *
     * @param string $id Block UUID
     *
     * @return StaticBlock|null
     */
    public function restore(string $id): ?StaticBlock;

    /**
     * Force delete a block permanently.
     *
     * @param string $id Block UUID
     *
     * @return bool
     */
    public function forceDelete(string $id): bool;

    /**
     * Duplicate a block with new identifier.
     *
     * @param string $id            Block UUID to duplicate
     * @param string $newIdentifier New identifier for the copy
     *
     * @return StaticBlock
     */
    public function duplicate(string $id, string $newIdentifier): StaticBlock;
}
