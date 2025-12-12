<?php

declare(strict_types=1);

namespace Modules\Core\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait Filterable
{
    /**
     * Apply filters from request.
     */
    public function scopeFilter(Builder $query, ?Request $request = null): Builder
    {
        $request = $request ?? request();
        $filters = $this->getFilters();

        foreach ($filters as $filter => $config) {
            $value = $request->input($filter);

            if ($value === null || $value === '') {
                continue;
            }

            $this->applyFilter($query, $filter, $value, $config);
        }

        return $query;
    }

    /**
     * Apply single filter.
     */
    protected function applyFilter(Builder $query, string $filter, mixed $value, array|string $config): void
    {
        // Simple column filter
        if (is_string($config)) {
            $query->where($config, $value);
            return;
        }

        $column = $config['column'] ?? $filter;
        $operator = $config['operator'] ?? '=';
        $type = $config['type'] ?? 'exact';

        match ($type) {
            'exact' => $query->where($column, $operator, $value),
            'like' => $query->where($column, 'LIKE', "%{$value}%"),
            'starts_with' => $query->where($column, 'LIKE', "{$value}%"),
            'ends_with' => $query->where($column, 'LIKE', "%{$value}"),
            'date' => $query->whereDate($column, $operator, $value),
            'date_range' => $this->applyDateRangeFilter($query, $column, $value),
            'in' => $query->whereIn($column, (array) $value),
            'not_in' => $query->whereNotIn($column, (array) $value),
            'null' => $value ? $query->whereNull($column) : $query->whereNotNull($column),
            'boolean' => $query->where($column, filter_var($value, FILTER_VALIDATE_BOOLEAN)),
            'between' => $query->whereBetween($column, (array) $value),
            'relation' => $this->applyRelationFilter($query, $config, $value),
            'scope' => $query->{$config['scope']}($value),
            'callback' => $config['callback']($query, $value),
            default => $query->where($column, $operator, $value),
        };
    }

    /**
     * Apply date range filter.
     */
    protected function applyDateRangeFilter(Builder $query, string $column, mixed $value): void
    {
        if (is_array($value)) {
            if (isset($value['from'])) {
                $query->whereDate($column, '>=', $value['from']);
            }
            if (isset($value['to'])) {
                $query->whereDate($column, '<=', $value['to']);
            }
        }
    }

    /**
     * Apply relation filter.
     */
    protected function applyRelationFilter(Builder $query, array $config, mixed $value): void
    {
        $relation = $config['relation'];
        $column = $config['relation_column'] ?? 'id';

        $query->whereHas($relation, function ($q) use ($column, $value) {
            $q->where($column, $value);
        });
    }

    /**
     * Apply sorting.
     */
    public function scopeSort(Builder $query, ?Request $request = null): Builder
    {
        $request = $request ?? request();

        $sortBy = $request->input('sort_by', $this->getDefaultSort());
        $sortDir = $request->input('sort_dir', $this->getDefaultSortDirection());

        // Validate sort column
        if (!in_array($sortBy, $this->getSortableColumns())) {
            $sortBy = $this->getDefaultSort();
        }

        // Validate sort direction
        $sortDir = strtolower($sortDir) === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sortBy, $sortDir);
    }

    /**
     * Apply search.
     */
    public function scopeSearch(Builder $query, ?string $term = null): Builder
    {
        $term = $term ?? request()->input('search');

        if (empty($term)) {
            return $query;
        }

        $searchable = $this->getSearchableColumns();

        return $query->where(function ($q) use ($term, $searchable) {
            foreach ($searchable as $column) {
                if (str_contains($column, '.')) {
                    // Relation search
                    [$relation, $relationColumn] = explode('.', $column, 2);
                    $q->orWhereHas($relation, function ($rq) use ($relationColumn, $term) {
                        $rq->where($relationColumn, 'LIKE', "%{$term}%");
                    });
                } else {
                    $q->orWhere($column, 'LIKE', "%{$term}%");
                }
            }
        });
    }

    /**
     * Get available filters.
     */
    protected function getFilters(): array
    {
        return $this->filters ?? [];
    }

    /**
     * Get searchable columns.
     */
    protected function getSearchableColumns(): array
    {
        return $this->searchable ?? ['name', 'title'];
    }

    /**
     * Get sortable columns.
     */
    protected function getSortableColumns(): array
    {
        return $this->sortable ?? ['id', 'created_at', 'updated_at'];
    }

    /**
     * Get default sort column.
     */
    protected function getDefaultSort(): string
    {
        return $this->defaultSort ?? 'created_at';
    }

    /**
     * Get default sort direction.
     */
    protected function getDefaultSortDirection(): string
    {
        return $this->defaultSortDirection ?? 'desc';
    }
}
