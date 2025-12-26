<?php

declare(strict_types=1);

namespace Modules\Search\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Search\Application\Services\SearchQueryService;
use Modules\Search\Domain\DTO\SearchQuery;
use Modules\Search\Http\Requests\SearchIndexRequest;
use Modules\Search\Http\Requests\SearchRequest;
use Modules\Search\Http\Requests\SuggestionsRequest;

class SearchController extends BaseController
{
    public function __construct(
        protected SearchQueryService $queryService
    ) {
    }

    /**
     * Perform a global search across all indices.
     *
     * @param SearchRequest $request The validated search request
     * @return JsonResponse Search results
     */
    public function search(SearchRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $searchQuery = new SearchQuery(
            query: $validated['q'],
            types: $validated['indices'] ?? [],
            limit: $validated['limit'] ?? config('search.per_page', 20),
        );
        
        $results = $this->queryService->search($searchQuery);

        return $this->success($results);
    }

    /**
     * Search within a specific index.
     *
     * @param SearchIndexRequest $request The validated search request
     * @param string $index The index name to search in
     * @return JsonResponse Search results from the specified index
     */
    public function searchIndex(SearchIndexRequest $request, string $index): JsonResponse
    {
        $validated = $request->validated();

        $results = $this->queryService->searchIndex(
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

    /**
     * Get search suggestions based on query.
     *
     * @param SuggestionsRequest $request The validated suggestions request
     * @return JsonResponse Array of suggestion strings
     */
    public function suggestions(SuggestionsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $results = $this->queryService->searchIndex(
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
