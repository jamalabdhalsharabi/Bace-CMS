<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

trait Searchable
{
    /**
     * Boot the trait.
     */
    public static function bootSearchable(): void
    {
        static::created(function ($model) {
            $model->indexForSearch();
        });

        static::updated(function ($model) {
            $model->indexForSearch();
        });

        static::deleted(function ($model) {
            $model->removeFromSearch();
        });
    }

    /**
     * Index model for search.
     */
    public function indexForSearch(): void
    {
        if (!$this->shouldIndex()) {
            return;
        }

        // Dispatch job to index
        dispatch(function () {
            $this->performIndexing();
        })->afterCommit();
    }

    /**
     * Remove model from search index.
     */
    public function removeFromSearch(): void
    {
        dispatch(function () {
            $this->performRemoval();
        })->afterCommit();
    }

    /**
     * Perform the actual indexing.
     */
    protected function performIndexing(): void
    {
        $searchService = app('search');

        if (!$searchService) {
            return;
        }

        $searchService->index(
            $this->getSearchIndex(),
            $this->getSearchKey(),
            $this->toSearchableArray()
        );
    }

    /**
     * Perform the actual removal.
     */
    protected function performRemoval(): void
    {
        $searchService = app('search');

        if (!$searchService) {
            return;
        }

        $searchService->delete(
            $this->getSearchIndex(),
            $this->getSearchKey()
        );
    }

    /**
     * Get searchable array representation.
     */
    public function toSearchableArray(): array
    {
        $data = $this->toArray();

        // Add computed fields
        $data['_type'] = $this->getSearchType();
        $data['_url'] = $this->getSearchUrl();

        // Include translations if available
        if (method_exists($this, 'translations')) {
            $data['_translations'] = $this->translations->mapWithKeys(function ($t) {
                return [$t->locale => $t->toArray()];
            })->toArray();
        }

        return $data;
    }

    /**
     * Check if model should be indexed.
     */
    protected function shouldIndex(): bool
    {
        // Override in model to add conditions
        // e.g., only index published content
        if (method_exists($this, 'isPublished')) {
            return $this->isPublished();
        }

        return true;
    }

    /**
     * Get search index name.
     */
    public function getSearchIndex(): string
    {
        return $this->searchIndex ?? $this->getTable();
    }

    /**
     * Get search key (document ID).
     */
    public function getSearchKey(): string
    {
        return (string) $this->getKey();
    }

    /**
     * Get search type identifier.
     */
    public function getSearchType(): string
    {
        return $this->searchType ?? class_basename($this);
    }

    /**
     * Get URL for search result.
     */
    public function getSearchUrl(): ?string
    {
        if (method_exists($this, 'getUrl')) {
            return $this->getUrl();
        }

        return null;
    }

    /**
     * Search this model.
     */
    public static function search(string $query, array $options = []): array
    {
        $instance = new static();
        $searchService = app('search');

        if (!$searchService) {
            return [];
        }

        return $searchService->search($instance->getSearchIndex(), [
            'q' => $query,
            ...$options,
        ]);
    }

    /**
     * Reindex all records.
     */
    public static function reindexAll(): int
    {
        $count = 0;

        static::query()->chunk(100, function ($models) use (&$count) {
            foreach ($models as $model) {
                $model->indexForSearch();
                $count++;
            }
        });

        return $count;
    }
}
