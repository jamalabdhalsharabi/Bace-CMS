<?php

declare(strict_types=1);

namespace Modules\Content\Application\Services;

use Modules\Content\Application\Actions\Page\CreatePageAction;
use Modules\Content\Application\Actions\Page\DeletePageAction;
use Modules\Content\Application\Actions\Page\DuplicatePageAction;
use Modules\Content\Application\Actions\Page\PublishPageAction;
use Modules\Content\Application\Actions\Page\UpdatePageAction;
use Modules\Content\Domain\DTO\PageData;
use Modules\Content\Domain\Models\Page;
use Modules\Content\Domain\Repositories\PageRepository;

/**
 * Page Command Service.
 */
final class PageCommandService
{
    public function __construct(
        private readonly CreatePageAction $createAction,
        private readonly UpdatePageAction $updateAction,
        private readonly DeletePageAction $deleteAction,
        private readonly PublishPageAction $publishAction,
        private readonly DuplicatePageAction $duplicateAction,
        private readonly PageRepository $repository,
    ) {}

    public function create(PageData $data): Page
    {
        return $this->createAction->execute($data);
    }

    public function update(Page $page, PageData $data): Page
    {
        return $this->updateAction->execute($page, $data);
    }

    public function publish(Page $page): Page
    {
        return $this->publishAction->execute($page);
    }

    public function unpublish(Page $page): Page
    {
        return $this->publishAction->unpublish($page);
    }

    public function archive(Page $page): Page
    {
        return $this->publishAction->archive($page);
    }

    public function delete(Page $page): bool
    {
        return $this->deleteAction->execute($page);
    }

    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            $this->repository->update($id, ['sort_order' => $index]);
        }
    }

    public function move(Page $page, ?string $parentId): Page
    {
        $this->repository->update($page->id, ['parent_id' => $parentId]);

        return $page->fresh();
    }

    public function duplicate(Page $page): Page
    {
        return $this->duplicateAction->execute($page);
    }
}
