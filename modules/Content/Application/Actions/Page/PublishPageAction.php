<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Page;

use Modules\Content\Domain\Models\Page;
use Modules\Content\Domain\Repositories\PageRepository;
use Modules\Core\Application\Actions\Action;

final class PublishPageAction extends Action
{
    public function __construct(
        private readonly PageRepository $repository
    ) {}

    public function execute(Page $page): Page
    {
        $this->repository->update($page->id, [
            'status' => 'published',
            'published_at' => $page->published_at ?? now(),
        ]);

        return $page->fresh();
    }

    public function unpublish(Page $page): Page
    {
        $this->repository->update($page->id, ['status' => 'draft']);

        return $page->fresh();
    }

    public function archive(Page $page): Page
    {
        $this->repository->update($page->id, ['status' => 'archived']);

        return $page->fresh();
    }
}
