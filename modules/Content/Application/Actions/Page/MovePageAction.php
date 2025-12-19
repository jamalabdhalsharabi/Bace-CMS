<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Page;

use Modules\Core\Application\Actions\Action;
use Modules\Content\Domain\Models\Page;

/**
 * Move Page Action.
 *
 * Moves a page to a different parent.
 *
 * @package Modules\Content\Application\Actions\Page
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class MovePageAction extends Action
{
    /**
     * Execute the move action.
     *
     * @param Page $page The page to move
     * @param string|null $parentId The new parent ID (null for root)
     *
     * @return Page The moved page
     */
    public function execute(Page $page, ?string $parentId): Page
    {
        $page->update(['parent_id' => $parentId]);
        return $page->fresh();
    }
}
