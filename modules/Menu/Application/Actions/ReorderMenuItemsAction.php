<?php

declare(strict_types=1);

namespace Modules\Menu\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Menu\Domain\Models\MenuItem;

/**
 * Reorder Menu Items Action.
 *
 * Updates the sort order of menu items.
 *
 * @package Modules\Menu\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ReorderMenuItemsAction extends Action
{
    /**
     * Execute the reorder action.
     *
     * @param array<int, string> $order Array of item IDs in desired order (index = sort_order)
     *
     * @return void
     */
    public function execute(array $order): void
    {
        foreach ($order as $index => $itemId) {
            MenuItem::where('id', $itemId)->update(['sort_order' => $index]);
        }
    }
}
