<?php

declare(strict_types=1);

namespace Modules\Content\Application\Services;

use Modules\Content\Application\Actions\Page\CreatePageAction;
use Modules\Content\Application\Actions\Page\DeletePageAction;
use Modules\Content\Application\Actions\Page\DuplicatePageAction;
use Modules\Content\Application\Actions\Page\MovePageAction;
use Modules\Content\Application\Actions\Page\PublishPageAction;
use Modules\Content\Application\Actions\Page\ReorderPagesAction;
use Modules\Content\Application\Actions\Page\UpdatePageAction;
use Modules\Content\Domain\DTO\PageData;
use Modules\Content\Domain\Models\Page;

/**
 * Page Command Service.
 *
 * Orchestrates all page write operations via Action classes.
 * No direct Model/Repository usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\Content\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PageCommandService
{
    /**
     * Create a new PageCommandService instance.
     *
     * @param CreatePageAction $createAction Action for creating pages
     * @param UpdatePageAction $updateAction Action for updating pages
     * @param DeletePageAction $deleteAction Action for deleting pages
     * @param PublishPageAction $publishAction Action for publishing pages
     * @param DuplicatePageAction $duplicateAction Action for duplicating pages
     * @param ReorderPagesAction $reorderAction Action for reordering pages
     * @param MovePageAction $moveAction Action for moving pages
     */
    public function __construct(
        private readonly CreatePageAction $createAction,
        private readonly UpdatePageAction $updateAction,
        private readonly DeletePageAction $deleteAction,
        private readonly PublishPageAction $publishAction,
        private readonly DuplicatePageAction $duplicateAction,
        private readonly ReorderPagesAction $reorderAction,
        private readonly MovePageAction $moveAction,
    ) {}

    /**
     * Create a new page.
     *
     * @param PageData $data The page data DTO
     *
     * @return Page The created page
     */
    public function create(PageData $data): Page
    {
        return $this->createAction->execute($data);
    }

    /**
     * Update an existing page.
     *
     * @param Page $page The page to update
     * @param PageData $data The updated page data
     *
     * @return Page The updated page
     */
    public function update(Page $page, PageData $data): Page
    {
        return $this->updateAction->execute($page, $data);
    }

    /**
     * Publish a page.
     *
     * @param Page $page The page to publish
     *
     * @return Page The published page
     */
    public function publish(Page $page): Page
    {
        return $this->publishAction->execute($page);
    }

    /**
     * Unpublish a page.
     *
     * @param Page $page The page to unpublish
     *
     * @return Page The unpublished page
     */
    public function unpublish(Page $page): Page
    {
        return $this->publishAction->unpublish($page);
    }

    /**
     * Archive a page.
     *
     * @param Page $page The page to archive
     *
     * @return Page The archived page
     */
    public function archive(Page $page): Page
    {
        return $this->publishAction->archive($page);
    }

    /**
     * Delete a page.
     *
     * @param Page $page The page to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(Page $page): bool
    {
        return $this->deleteAction->execute($page);
    }

    /**
     * Reorder pages.
     *
     * @param array<int, string> $order Array of page IDs in desired order
     *
     * @return void
     */
    public function reorder(array $order): void
    {
        $this->reorderAction->execute($order);
    }

    /**
     * Move a page to a different parent.
     *
     * @param Page $page The page to move
     * @param string|null $parentId The new parent ID
     *
     * @return Page The moved page
     */
    public function move(Page $page, ?string $parentId): Page
    {
        return $this->moveAction->execute($page, $parentId);
    }

    /**
     * Duplicate a page.
     *
     * @param Page $page The page to duplicate
     *
     * @return Page The duplicated page
     */
    public function duplicate(Page $page): Page
    {
        return $this->duplicateAction->execute($page);
    }
}
