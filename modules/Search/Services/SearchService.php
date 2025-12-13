<?php

declare(strict_types=1);

namespace Modules\Search\Services;

use Modules\Search\Contracts\SearchEngineContract;
use Modules\Search\Contracts\SearchServiceContract;

/**
 * Class SearchService
 *
 * Service class for search functionality including
 * global search, index-specific search, and reindexing.
 *
 * @package Modules\Search\Services
 */
class SearchService implements SearchServiceContract
{
    /**
     * The search engine instance.
     *
     * @var SearchEngineContract
     */
    protected SearchEngineContract $engine;

    /**
     * Create a new SearchService instance.
     *
     * @param SearchEngineContract $engine The search engine
     */
    public function __construct(
        SearchEngineContract $engine
    ) {
        $this->engine = $engine;
    }

    /**
     * {@inheritdoc}
     */
    public function search(string $query, array $options = []): array
    {
        $indices = $options['indices'] ?? array_keys(config('search.indices', []));
        $results = [];

        foreach ($indices as $index) {
            $indexResults = $this->searchIndex($index, $query, $options);
            $results[$index] = $indexResults;
        }

        return [
            'query' => $query,
            'results' => $results,
            'total' => array_sum(array_column($results, 'total')),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function searchIndex(string $index, string $query, array $options = []): array
    {
        $searchOptions = [
            'q' => $query,
            'limit' => $options['limit'] ?? config('search.per_page', 20),
            'offset' => $options['offset'] ?? 0,
        ];

        if (!empty($options['filter'])) {
            $searchOptions['filter'] = $options['filter'];
        }

        if (!empty($options['sort'])) {
            $searchOptions['sort'] = $options['sort'];
        }

        return $this->engine->search($index, $searchOptions);
    }

    /**
     * {@inheritdoc}
     */
    public function indexModel(object $model): bool
    {
        if (!method_exists($model, 'toSearchableArray')) {
            return false;
        }

        $index = $this->getIndexForModel($model);

        if (!$index) {
            return false;
        }

        return $this->engine->index($index, (string) $model->getKey(), $model->toSearchableArray());
    }

    /**
     * {@inheritdoc}
     */
    public function removeModel(object $model): bool
    {
        $index = $this->getIndexForModel($model);

        if (!$index) {
            return false;
        }

        return $this->engine->delete($index, (string) $model->getKey());
    }

    /**
     * {@inheritdoc}
     */
    public function reindex(string $index): int
    {
        $config = config("search.indices.{$index}");

        if (!$config || !isset($config['model'])) {
            return 0;
        }

        $this->engine->flush($index);

        $modelClass = $config['model'];
        $count = 0;

        $modelClass::query()->chunk(100, function ($models) use ($index, &$count) {
            foreach ($models as $model) {
                $data = method_exists($model, 'toSearchableArray')
                    ? $model->toSearchableArray()
                    : $model->toArray();

                $this->engine->index($index, (string) $model->getKey(), $data);
                $count++;
            }
        });

        return $count;
    }

    /**
     * {@inheritdoc}
     */
    public function reindexAll(): array
    {
        $results = [];

        foreach (array_keys(config('search.indices', [])) as $index) {
            $results[$index] = $this->reindex($index);
        }

        return $results;
    }

    protected function getIndexForModel(object $model): ?string
    {
        $modelClass = get_class($model);

        foreach (config('search.indices', []) as $index => $config) {
            if (isset($config['model']) && $config['model'] === $modelClass) {
                return $index;
            }
        }

        return null;
    }
}
