<?php

declare(strict_types=1);

namespace Modules\Search\Application\Services;

use Illuminate\Support\Collection;
use Modules\Search\Domain\DTO\SearchQuery;

/**
 * Search Query Service.
 *
 * Handles global search operations across multiple content types.
 * Searches articles, products, projects, services, and events.
 *
 * @package Modules\Search\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class SearchQueryService
{
    /**
     * @var array<string, class-string> Searchable model mappings
     */
    private array $searchables = [];

    /**
     * Create a new SearchQueryService instance.
     */
    public function __construct()
    {
        $this->searchables = config('search.searchables', [
            'articles' => \Modules\Content\Domain\Models\Article::class,
            'products' => \Modules\Products\Domain\Models\Product::class,
            'projects' => \Modules\Projects\Domain\Models\Project::class,
            'services' => \Modules\Services\Domain\Models\Service::class,
            'events' => \Modules\Events\Domain\Models\Event::class,
        ]);
    }

    public function search(SearchQuery $query): Collection
    {
        $results = collect();
        $types = empty($query->types) ? array_keys($this->searchables) : $query->types;

        foreach ($types as $type) {
            if (!isset($this->searchables[$type])) {
                continue;
            }

            $model = $this->searchables[$type];
            $items = $this->searchModel($model, $query);

            foreach ($items as $item) {
                $results->push([
                    'type' => $type,
                    'id' => $item->id,
                    'title' => $this->getTitle($item),
                    'description' => $this->getDescription($item),
                    'url' => $this->getUrl($type, $item),
                    'score' => $item->search_score ?? 0,
                ]);
            }
        }

        return $results
            ->sortByDesc('score')
            ->take($query->limit)
            ->values();
    }

    public function suggest(string $query, int $limit = 5): array
    {
        $suggestions = [];

        foreach ($this->searchables as $type => $model) {
            $items = $model::query()
                ->whereHas('translations', fn ($q) => 
                    $q->where('title', 'LIKE', "{$query}%")
                )
                ->limit($limit)
                ->get();

            foreach ($items as $item) {
                $suggestions[] = $this->getTitle($item);
            }
        }

        return array_unique(array_slice($suggestions, 0, $limit));
    }

    private function searchModel(string $model, SearchQuery $query): Collection
    {
        $searchTerm = $query->query;

        try {
            return $model::query()
                ->where('status', 'published')
                ->whereHas('translations', fn ($tq) => 
                    $tq->where('title', 'LIKE', "%{$searchTerm}%")
                )
                ->limit($query->limit)
                ->get();
        } catch (\Exception $e) {
            return collect();
        }
    }

    private function getTitle($item): string
    {
        return $item->translation?->title 
            ?? $item->title 
            ?? $item->name 
            ?? '';
    }

    private function getDescription($item): ?string
    {
        return $item->translation?->description 
            ?? $item->description 
            ?? null;
    }

    private function getUrl(string $type, $item): string
    {
        $slug = $item->translation?->slug ?? $item->slug ?? $item->id;

        return "/{$type}/{$slug}";
    }
}
