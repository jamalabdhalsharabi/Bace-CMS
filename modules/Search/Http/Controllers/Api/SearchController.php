<?php

declare(strict_types=1);

namespace Modules\Search\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Search\Contracts\SearchServiceContract;
use Modules\Search\Http\Requests\SearchIndexRequest;
use Modules\Search\Http\Requests\SearchRequest;
use Modules\Search\Http\Requests\SuggestionsRequest;

class SearchController extends BaseController
{
    public function __construct(
        protected SearchServiceContract $searchService
    ) {}

    public function search(SearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $results = $this->searchService->search(
            $validated['q'],
            [
                'indices' => $validated['indices'] ?? null,
                'limit' => $validated['limit'] ?? config('search.per_page', 20),
                'offset' => $validated['offset'] ?? 0,
            ]
        );

        return $this->success($results);
    }

    public function searchIndex(SearchIndexRequest $request, string $index): JsonResponse
    {
        $validated = $request->validated();

        $results = $this->searchService->searchIndex(
            $index,
            $validated['q'],
            [
                'limit' => $validated['limit'] ?? config('search.per_page', 20),
                'offset' => $validated['offset'] ?? 0,
                'filter' => $validated['filter'] ?? null,
                'sort' => $validated['sort'] ?? null,
            ]
        );

        return $this->success($results);
    }

    public function suggestions(SuggestionsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $results = $this->searchService->searchIndex(
            $validated['index'] ?? 'articles',
            $validated['q'],
            ['limit' => $validated['limit'] ?? 5]
        );

        $suggestions = collect($results['hits'] ?? [])
            ->pluck('title')
            ->filter()
            ->values()
            ->toArray();

        return $this->success(['suggestions' => $suggestions]);
    }
}
