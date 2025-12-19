<?php

declare(strict_types=1);

namespace Modules\Menu\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Menu\Domain\Models\MenuItem;

/**
 * Update Menu Item Action.
 *
 * Updates an existing menu item with new data.
 *
 * @package Modules\Menu\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UpdateMenuItemAction extends Action
{
    /**
     * Execute the update action.
     *
     * @param string $itemId The ID of the menu item to update
     * @param array<string, mixed> $data The data to update
     *
     * @return MenuItem The updated menu item
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If item not found
     */
    public function execute(string $itemId, array $data): MenuItem
    {
        $item = MenuItem::findOrFail($itemId);
        $item->update($data);
        return $item->fresh();
    }
}
