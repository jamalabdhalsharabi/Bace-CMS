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
     * Execute the page reordering action.
     *
     * Updates sort_order for multiple pages based on array position.
     *
     * @param array<int, string> $order Array of page UUIDs in desired display order
     * 
     * @return void
     * 
     * @throws \Exception When reordering fails
     */
    public function execute(array $order): void
    {
        foreach ($order as $index => $id) {
            Page::where('id', $id)->update(['sort_order' => $index]);
        }
    }
}
