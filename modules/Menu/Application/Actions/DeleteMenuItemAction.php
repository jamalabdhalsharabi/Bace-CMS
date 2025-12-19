<?php

declare(strict_types=1);

namespace Modules\Menu\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Menu\Domain\Models\MenuItem;

/**
 * Delete Menu Item Action.
 *
 * Removes a menu item and its children from the menu.
 *
 * @package Modules\Menu\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class DeleteMenuItemAction extends Action
{
    /**
     * Execute the delete action.
     *
     * @param string $itemId The ID of the menu item to delete
     *
     * @return bool True if deletion was successful
     */
    public function execute(string $itemId): bool
    {
        return MenuItem::where('id', $itemId)->delete() > 0;
    }
}
