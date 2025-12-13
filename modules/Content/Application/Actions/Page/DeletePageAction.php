<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Page;

use Modules\Content\Domain\Models\Page;
use Modules\Content\Domain\Repositories\PageRepository;
use Modules\Core\Application\Actions\Action;

final class DeletePageAction extends Action
{
    public function __construct(
        private readonly PageRepository $repository
    ) {}

    public function execute(Page $page): bool
    {
        Page::where('parent_id', $page->id)->update(['parent_id' => $page->parent_id]);
        $page->update(['deleted_by' => $this->userId()]);

        return $this->repository->delete($page->id);
    }
}
