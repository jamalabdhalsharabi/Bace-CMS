<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Core\Domain\Repositories\BaseRepository;
use Modules\StaticBlocks\Domain\Contracts\StaticBlockRepositoryInterface;
use Modules\StaticBlocks\Domain\Models\StaticBlock;

/**
 * Static Block Repository Implementation.
 *
 * @extends BaseRepository<StaticBlock>
 * @implements StaticBlockRepositoryInterface
 *
 * @package Modules\StaticBlocks\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class StaticBlockRepository extends BaseRepository implements StaticBlockRepositoryInterface
{
    public function __construct(StaticBlock $model)
    {
        parent::__construct($model);
    }

    /**
     * {@inheritdoc}
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->query()->with('translation');

        if (isset($filters['active'])) {
            $query->where('status', $filters['active'] ? 'published' : 'draft');
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('identifier', 'LIKE', "%{$search}%")
                  ->orWhereHas('translations', fn ($t) => $t->where('title', 'LIKE', "%{$search}%"));
            });
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function findByIdentifier(string $identifier): ?StaticBlock
    {
        return $this->query()
            ->where('identifier', $identifier)
            ->where('status', 'published')
            ->with('translation')
            ->first();
    }

    /**
     * {@inheritdoc}
     */
    public function getActive(): Collection
    {
        return $this->query()
            ->where('status', 'published')
            ->with('translation')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getByType(string $type): Collection
    {
        return $this->query()
            ->where('type', $type)
            ->where('status', 'published')
            ->with('translation')
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function getTrashed(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->newQuery()
            ->onlyTrashed()
            ->with('translation')
            ->latest('deleted_at')
            ->paginate($perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function restore(string $id): ?StaticBlock
    {
        $block = $this->model->newQuery()->withTrashed()->find($id);
        $block?->restore();

        return $block;
    }

    /**
     * {@inheritdoc}
     */
    public function forceDelete(string $id): bool
    {
        $block = $this->model->newQuery()->withTrashed()->find($id);

        return $block?->forceDelete() ?? false;
    }

    /**
     * {@inheritdoc}
     */
    public function duplicate(string $id, string $newIdentifier): StaticBlock
    {
        $original = $this->query()->with('translations')->findOrFail($id);

        return DB::transaction(function () use ($original, $newIdentifier) {
            $clone = $original->replicate();
            $clone->identifier = $newIdentifier;
            $clone->status = 'draft';
            $clone->save();

            foreach ($original->translations as $trans) {
                $clone->translations()->create($trans->only(['locale', 'title', 'content']));
            }

            return $clone->fresh(['translations']);
        });
    }
}
