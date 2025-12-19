<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\StaticBlocks\Domain\Models\StaticBlock;

/**
 * Restore Static Block Action.
 *
 * Restores a soft-deleted static block.
 *
 * @package Modules\StaticBlocks\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class RestoreStaticBlockAction extends Action
{
    /**
     * Execute the restore action.
     *
     * @param string $id The ID of the block to restore
     *
     * @return StaticBlock|null The restored block or null if not found
     */
    public function execute(string $id): ?StaticBlock
    {
        $block = StaticBlock::withTrashed()->find($id);
        $block?->restore();
        return $block;
    }
}
