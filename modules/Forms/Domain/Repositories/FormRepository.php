<?php

declare(strict_types=1);

namespace Modules\Forms\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Forms\Domain\Models\Form;

/**
 * Form Repository.
 *
 * @extends BaseRepository<Form>
 */
final class FormRepository extends BaseRepository
{
    public function __construct(Form $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()->withCount('submissions');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'LIKE', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    public function findBySlug(string $slug): ?Form
    {
        return $this->query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();
    }

    public function getActive(): Collection
    {
        return $this->query()
            ->where('is_active', true)
            ->get();
    }
}
