<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Page;

use Modules\Content\Domain\Models\Page;
use Modules\Content\Domain\Repositories\PageRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Delete Page Action.
 *
 * Handles soft deletion of pages with proper handling of child pages.
 * Reassigns child pages to deleted page's parent to maintain hierarchy.
 *
 * @package Modules\Content\Application\Actions\Page
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class DeletePageAction extends Action
{
    /**
     * Create a new DeletePageAction instance.
     *
     * @param PageRepository $repository The page repository for data operations
     */
    public function __construct(
        private readonly PageRepository $repository
    ) {}

    /**
     * Execute the page deletion action.
     *
     * Soft deletes page and reassigns child pages to maintain hierarchy.
     *
     * @param Page $page The page instance to delete
     * 
     * @return bool True if deletion was successful
     * 
     * @throws \Exception When deletion fails
     */
    public function execute(Page $page): bool
    {
        Page::where('parent_id', $page->id)->update(['parent_id' => $page->parent_id]);
        $page->update(['deleted_by' => $this->userId()]);

        return $this->repository->delete($page->id);
    }
}
