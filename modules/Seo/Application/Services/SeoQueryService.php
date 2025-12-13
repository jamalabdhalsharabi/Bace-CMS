<?php

declare(strict_types=1);

namespace Modules\Seo\Application\Services;

use Illuminate\Database\Eloquent\Collection;
use Modules\Seo\Domain\Models\PageView;
use Modules\Seo\Domain\Models\SearchLog;
use Modules\Seo\Domain\Repositories\RedirectRepository;
use Modules\Seo\Domain\Repositories\SeoMetaRepository;

final class SeoQueryService
{
    public function __construct(
        private readonly SeoMetaRepository $metaRepository,
        private readonly RedirectRepository $redirectRepository
    ) {}

    public function getMeta(string $entityType, string $entityId): ?object
    {
        return $this->metaRepository->findByEntity($entityType, $entityId);
    }

    public function getActiveRedirects(): Collection
    {
        return $this->redirectRepository->getActive();
    }

    public function findRedirect(string $url): ?object
    {
        return $this->redirectRepository->findBySourceUrl($url);
    }

    public function getPageViewStats(?string $from = null, ?string $to = null): array
    {
        $query = PageView::query();

        if ($from) $query->where('viewed_at', '>=', $from);
        if ($to) $query->where('viewed_at', '<=', $to);

        return [
            'total' => $query->count(),
            'unique_visitors' => $query->distinct('ip_address')->count(),
            'top_pages' => $query->selectRaw('url, count(*) as views')
                ->groupBy('url')
                ->orderByDesc('views')
                ->limit(10)
                ->get(),
        ];
    }

    public function getSearchStats(?int $limit = 20): Collection
    {
        return SearchLog::selectRaw('query, count(*) as count')
            ->groupBy('query')
            ->orderByDesc('count')
            ->limit($limit)
            ->get();
    }
}
