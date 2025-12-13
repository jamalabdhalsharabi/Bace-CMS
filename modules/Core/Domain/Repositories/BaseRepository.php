<?php

declare(strict_types=1);

namespace Modules\Core\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Modules\Core\Domain\Contracts\RepositoryInterface;

/**
 * Base Repository Implementation.
 *
 * Provides common data access methods for all repositories.
 * Extend this class to create specific repository implementations.
 *
 * @template TModel of Model
 * @implements RepositoryInterface<TModel>
 */
abstract class BaseRepository implements RepositoryInterface
{
    /**
     * The model instance.
     *
     * @var TModel
     */
    protected Model $model;

    /**
     * Relations to eager load.
     *
     * @var array<string>
     */
    protected array $with = [];

    /**
     * Create a new repository instance.
     *
     * @param TModel $model The model instance
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * {@inheritdoc}
     */
    public function all(array $columns = ['*']): Collection
    {
        return $this->query()->get($columns);
    }

    /**
     * {@inheritdoc}
     */
    public function paginate(int $perPage = 15, array $columns = ['*']): LengthAwarePaginator
    {
        return $this->query()->paginate($perPage, $columns);
    }

    /**
     * {@inheritdoc}
     */
    public function find(string $id): ?Model
    {
        return $this->query()->find($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findOrFail(string $id): Model
    {
        return $this->query()->findOrFail($id);
    }

    /**
     * {@inheritdoc}
     */
    public function findBy(string $field, mixed $value): Collection
    {
        return $this->query()->where($field, $value)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findFirstBy(string $field, mixed $value): ?Model
    {
        return $this->query()->where($field, $value)->first();
    }

    /**
     * {@inheritdoc}
     */
    public function create(array $data): Model
    {
        return $this->model->newInstance()->create($data);
    }

    /**
     * {@inheritdoc}
     */
    public function update(string $id, array $data): Model
    {
        $model = $this->findOrFail($id);
        $model->update($data);

        return $model->fresh();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    /**
     * {@inheritdoc}
     */
    public function query(): Builder
    {
        $query = $this->model->newQuery();

        if (!empty($this->with)) {
            $query->with($this->with);
        }

        return $query;
    }

    /**
     * {@inheritdoc}
     */
    public function with(array|string $relations): static
    {
        $this->with = is_array($relations) ? $relations : [$relations];

        return $this;
    }

    /**
     * Reset eager loading.
     *
     * @return static
     */
    public function resetWith(): static
    {
        $this->with = [];

        return $this;
    }
}
