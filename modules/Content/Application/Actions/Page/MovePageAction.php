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
     * Execute the page move action.
     *
     * Moves page to different parent in hierarchy, updating depth and path.
     *
     * @param Page $page The page instance to move
     * @param string|null $parentId The new parent UUID (null for root level)
     * 
     * @return Page The moved page with updated hierarchy
     * 
     * @throws \Exception When move operation fails
     */
    public function execute(Page $page, ?string $parentId): Page
    {
        $page->update(['parent_id' => $parentId]);
        return $page->fresh();
    }
}
