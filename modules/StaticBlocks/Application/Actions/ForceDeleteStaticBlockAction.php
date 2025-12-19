<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\StaticBlocks\Domain\Models\StaticBlock;

/**
 * Force Delete Static Block Action.
 *
 * Permanently deletes a static block.
 *
 * @package Modules\StaticBlocks\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ForceDeleteStaticBlockAction extends Action
{
    /**
     * Execute the force delete action.
     *
     * @param string $id The ID of the block to permanently delete
     *
     * @return bool True if deletion was successful
     */
    public function execute(string $id): bool
    {
        $block = StaticBlock::withTrashed()->find($id);
        return $block?->forceDelete() ?? false;
    }
}
