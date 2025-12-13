<?php

declare(strict_types=1);

namespace Modules\Search\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Search\Contracts\SearchServiceContract;
use Modules\Search\Http\Requests\SearchIndexRequest;
use Modules\Search\Http\Requests\SearchRequest;
use Modules\Search\Http\Requests\SuggestionsRequest;

/**
 * Class SearchController
 *
 * API controller for search functionality including
 * global search, index-specific search, and suggestions.
 *
 * @package Modules\Search\Http\Controllers\Api
 */
class SearchController extends BaseController
{
    /**
     * The search service instance.
     *
     * @var SearchServiceContract
     */
    protected SearchServiceContract $searchService;

    /**
     * Create a new SearchController instance.
     *
     * @param SearchServiceContract $searchService The search service implementation
     */
    public function __construct(
        SearchServiceContract $searchService
    ) {
        $this->searchService = $searchService;
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

    /**
     * Get search suggestions based on query.
     *
     * @param SuggestionsRequest $request The validated suggestions request
     * @return JsonResponse Array of suggestion strings
     */
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
