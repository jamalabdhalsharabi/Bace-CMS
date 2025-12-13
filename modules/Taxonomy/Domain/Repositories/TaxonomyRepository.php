<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Domain\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\Taxonomy\Domain\Models\Taxonomy;

/**
 * Taxonomy Repository.
 *
 * @extends BaseRepository<Taxonomy>
 */
final class TaxonomyRepository extends BaseRepository
{
    public function __construct(Taxonomy $model)
    {
        parent::__construct($model);
    }

    public function getByType(string $typeId, ?string $parentId = null): Collection
    {
        $query = $this->query()->where('type_id', $typeId);

        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }

        return $query->ordered()->get();
    }

    public function getTree(string $typeId): Collection
    {
        return $this->query()
            ->where('type_id', $typeId)
            ->whereNull('parent_id')
            ->with(['children.children', 'translation'])
            ->ordered()
            ->get();
    }

    public function findBySlug(string $slug, string $typeId): ?Taxonomy
    {
        return $this->query()
            ->where('type_id', $typeId)
            ->whereHas('translations', fn ($q) => $q->where('slug', $slug))
            ->first();
    }

    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            $this->model->where('id', $id)->update(['sort_order' => $index]);
        }
    }
}
