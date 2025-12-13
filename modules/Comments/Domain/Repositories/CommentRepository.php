<?php

declare(strict_types=1);

namespace Modules\Comments\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Domain\Repositories\BaseRepository;

/**
 * Comment Repository.
 *
 * @extends BaseRepository<Comment>
 */
final class CommentRepository extends BaseRepository
{
    public function __construct(Comment $model)
    {
        parent::__construct($model);
    }

    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->query();

        if (!empty($filters['commentable_type'])) {
            $query->where('commentable_type', $filters['commentable_type']);
        }

        if (!empty($filters['commentable_id'])) {
            $query->where('commentable_id', $filters['commentable_id']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['root']) && $filters['root']) {
            $query->whereNull('parent_id');
        }

        return $query->latest()->paginate($perPage);
    }

    public function getForCommentable(string $type, string $id): Collection
    {
        return $this->query()
            ->where('commentable_type', $type)
            ->where('commentable_id', $id)
            ->where('status', 'approved')
            ->whereNull('parent_id')
            ->with(['replies' => fn ($q) => $q->where('status', 'approved'), 'user'])
            ->oldest()
            ->get();
    }

    public function getPending(): Collection
    {
        return $this->query()
            ->where('status', 'pending')
            ->with(['user', 'commentable'])
            ->latest()
            ->get();
    }

    public function getReported(): Collection
    {
        return $this->query()
            ->where('is_reported', true)
            ->with(['user', 'commentable'])
            ->latest()
            ->get();
    }
}
