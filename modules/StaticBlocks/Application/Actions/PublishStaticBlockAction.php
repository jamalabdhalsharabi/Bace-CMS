<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\StaticBlocks\Domain\Models\StaticBlock;

/**
 * Publish Static Block Action.
 *
 * Publishes or unpublishes a static block.
 *
 * @package Modules\StaticBlocks\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PublishStaticBlockAction extends Action
{
    /**
     * Publish a static block.
     *
     * @param StaticBlock $block The block to publish
     *
     * @return StaticBlock The published block
     */
    public function execute(StaticBlock $block): StaticBlock
    {
        $block->update(['is_active' => true, 'published_at' => now()]);
        StaticBlock::clearCache($block->identifier);
        return $block->fresh();
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
        $block->update(['is_active' => false]);
        StaticBlock::clearCache($block->identifier);
        return $block->fresh();
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
        $block->update(['status' => 'archived']);
        StaticBlock::clearCache($block->identifier);
        return $block->fresh();
    }
}
