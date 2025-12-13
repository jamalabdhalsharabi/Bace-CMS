<?php

declare(strict_types=1);

namespace Modules\Content\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Content\Domain\Models\Page;
use Modules\Content\Domain\Repositories\PageRepository;

/**
 * Page Query Service.
 */
final class PageQueryService
{
    public function __construct(
        private readonly PageRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository
            ->with(['translation', 'featuredImage', 'parent'])
            ->getPaginated($filters, $perPage);
    }

    public function find(string $id): ?Page
    {
        return $this->repository
            ->with(['translations', 'featuredImage', 'parent', 'children'])
            ->find($id);
    }

    public function findBySlug(string $slug, ?string $locale = null): ?Page
    {
        return $this->repository
            ->with(['translations', 'featuredImage'])
            ->findBySlug($slug, $locale);
    }

    public function getPublished(): Collection
    {
        return $this->repository
            ->with(['translation'])
            ->getPublished();
    }

    public function getMenuPages(): Collection
    {
        return $this->repository->getMenuPages();
    }

    public function getTree(): Collection
    {
        return $this->repository
            ->with(['translation'])
            ->getTree();
    }
}
