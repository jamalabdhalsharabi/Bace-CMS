<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Application\Services;

use Modules\StaticBlocks\Application\Actions\CreateStaticBlockAction;
use Modules\StaticBlocks\Application\Actions\DeleteStaticBlockAction;
use Modules\StaticBlocks\Application\Actions\DuplicateStaticBlockAction;
use Modules\StaticBlocks\Application\Actions\ForceDeleteStaticBlockAction;
use Modules\StaticBlocks\Application\Actions\PublishStaticBlockAction;
use Modules\StaticBlocks\Application\Actions\RestoreStaticBlockAction;
use Modules\StaticBlocks\Application\Actions\UpdateStaticBlockAction;
use Modules\StaticBlocks\Domain\Models\StaticBlock;

/**
 * Static Block Command Service.
 *
 * Orchestrates all write operations for static blocks via Action classes.
 * No direct Model/Repository usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\StaticBlocks\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class StaticBlockCommandService
{
    /**
     * Create a new StaticBlockCommandService instance.
     *
     * @param CreateStaticBlockAction $createAction Action for creating blocks
     * @param UpdateStaticBlockAction $updateAction Action for updating blocks
     * @param DeleteStaticBlockAction $deleteAction Action for deleting blocks
     * @param PublishStaticBlockAction $publishAction Action for publishing blocks
     * @param DuplicateStaticBlockAction $duplicateAction Action for duplicating blocks
     * @param RestoreStaticBlockAction $restoreAction Action for restoring blocks
     * @param ForceDeleteStaticBlockAction $forceDeleteAction Action for force deleting blocks
     */
    public function __construct(
        private readonly CreateStaticBlockAction $createAction,
        private readonly UpdateStaticBlockAction $updateAction,
        private readonly DeleteStaticBlockAction $deleteAction,
        private readonly PublishStaticBlockAction $publishAction,
        private readonly DuplicateStaticBlockAction $duplicateAction,
        private readonly RestoreStaticBlockAction $restoreAction,
        private readonly ForceDeleteStaticBlockAction $forceDeleteAction,
    ) {}

    /**
     * Create a new static block.
     *
     * @param array<string, mixed> $data The block data
     *
     * @return StaticBlock The created block
     */
    public function create(array $data): StaticBlock
    {
        return $this->createAction->execute($data);
    }

    /**
     * Update an existing static block.
     *
     * @param StaticBlock $block The block to update
     * @param array<string, mixed> $data The updated data
     *
     * @return StaticBlock The updated block
     */
    public function update(StaticBlock $block, array $data): StaticBlock
    {
        return $this->updateAction->execute($block, $data);
    }

    /**
     * Delete a static block.
     *
     * @param StaticBlock $block The block to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(StaticBlock $block): bool
    {
        return $this->deleteAction->execute($block);
    }

    /**
     * Force delete a static block permanently.
     *
     * @param string $id The block ID
     *
     * @return bool True if deletion was successful
     */
    public function forceDelete(string $id): bool
    {
        return $this->forceDeleteAction->execute($id);
    }

    /**
     * Restore a soft-deleted static block.
     *
     * @param string $id The block ID
     *
     * @return StaticBlock|null The restored block
     */
    public function restore(string $id): ?StaticBlock
    {
        return $this->restoreAction->execute($id);
    }

    /**
     * Publish a static block.
     *
     * @param StaticBlock $block The block to publish
     *
     * @return StaticBlock The published block
     */
    public function publish(StaticBlock $block): StaticBlock
    {
        return $this->publishAction->execute($block);
    }

    /**
     * Unpublish a static block.
     *
     * @param StaticBlock $block The block to unpublish
     *
     * @return StaticBlock The unpublished block
     */
    public function unpublish(StaticBlock $block): StaticBlock
    {
        return $this->publishAction->unpublish($block);
    }

    /**
     * Duplicate a static block.
     *
     * @param StaticBlock $block The block to duplicate
     * @param string $newIdentifier The new identifier
     *
     * @return StaticBlock The duplicated block
     */
    public function duplicate(StaticBlock $block, string $newIdentifier): StaticBlock
    {
        return $this->duplicateAction->execute($block, $newIdentifier);
    }

    /**
     * Archive a static block.
     *
     * @param StaticBlock $block The block to archive
     *
     * @return StaticBlock The archived block
     */
    public function archive(StaticBlock $block): StaticBlock
    {
        return $this->publishAction->archive($block);
    }

    /**
     * Import a static block from external data.
     *
     * @param array<string, mixed> $data The import data
     *
     * @return StaticBlock The imported block
     */
    public function import(array $data): StaticBlock
    {
        return $this->createAction->execute([
            'identifier' => $data['identifier'],
            'type' => $data['type'] ?? 'html',
            'settings' => $data['settings'] ?? [],
            'is_active' => false,
            'translations' => $data['translations'],
        ]);
    }
}
