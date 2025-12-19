<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Page;

use Modules\Core\Application\Actions\Action;
use Modules\Content\Domain\Models\Page;

/**
 * Reorder Pages Action.
 *
 * Updates the sort order of pages.
 *
 * @package Modules\Content\Application\Actions\Page
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ReorderPagesAction extends Action
{
    /**
     * Execute the reorder action.
     *
     * @param array<int, string> $order Array of page IDs in desired order
     *
     * @return void
     */
    public function execute(array $order): void
    {
        foreach ($order as $index => $id) {
            Page::where('id', $id)->update(['sort_order' => $index]);
        }
    }
}
