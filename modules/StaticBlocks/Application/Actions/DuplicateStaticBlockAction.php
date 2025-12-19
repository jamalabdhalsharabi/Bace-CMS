<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Application\Actions;

use Illuminate\Support\Facades\DB;
use Modules\Core\Application\Actions\Action;
use Modules\StaticBlocks\Domain\Models\StaticBlock;

/**
 * Duplicate Static Block Action.
 *
 * Duplicates a static block with a new identifier.
 *
 * @package Modules\StaticBlocks\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class DuplicateStaticBlockAction extends Action
{
    /**
     * Execute the duplicate action.
     *
     * @param StaticBlock $block The block to duplicate
     * @param string $newIdentifier The new identifier for the duplicate
     *
     * @return StaticBlock The duplicated block
     */
    public function execute(StaticBlock $block, string $newIdentifier): StaticBlock
    {
        return DB::transaction(function () use ($block, $newIdentifier) {
            $clone = $block->replicate();
            $clone->identifier = $newIdentifier;
            $clone->is_active = false;
            $clone->save();

            foreach ($block->translations as $trans) {
                $clone->translations()->create($trans->only(['locale', 'title', 'content']));
            }

            return $clone->fresh(['translations']);
        });
    }
}
