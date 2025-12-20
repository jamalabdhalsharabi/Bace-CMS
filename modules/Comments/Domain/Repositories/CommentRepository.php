<?php

declare(strict_types=1);

namespace Modules\Comments\Domain\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Domain\Repositories\BaseRepository;

/**
 * Comment Repository.
 *
 * Read-only repository for Comment model queries.
 * All write operations (create, update, delete) must be performed
 * through Action classes, not through this repository.
 *
 * @extends BaseRepository<Comment>
 *
 * @package Modules\Comments\Domain\Repositories
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CommentRepository extends BaseRepository
{
    /**
     * Create a new CommentRepository instance.
     *
     * @param Comment $model The Comment model instance
     */
    public function __construct(Comment $model)
    {
        parent::__construct($model);
    }

    /**
     * Get paginated comments with optional filters.
     *
     * Uses eager loading for user and commentable relationships
     * to minimize N+1 query issues.
     *
     * @param array<string, mixed> $filters Available filters: commentable_type, commentable_id, status, user_id, root
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Comment>
     */
    public function getPaginated(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->buildFilteredQuery($filters)
            ->with(['user', 'commentable'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get approved root comments for a commentable entity.
     *
     * Includes approved replies with eager loading to prevent N+1 queries.
     *
     * @param string $type The commentable type (e.g., 'App\Models\Article')
     * @param string $id The commentable ID
     *
     * @return Collection<int, Comment>
     */
    public function getForCommentable(string $type, string $id): Collection
    {
        return $this->query()
            ->where('commentable_type', $type)
            ->where('commentable_id', $id)
            ->where('status', 'approved')
            ->whereNull('parent_id')
            ->with(['replies' => fn ($q) => $q->where('status', 'approved')->with('user'), 'user'])
            ->oldest()
            ->get();
    }

    /**
     * Get all pending comments awaiting moderation.
     *
     * @return Collection<int, Comment>
     */
    public function getPending(): Collection
    {
        return $this->query()
            ->where('status', 'pending')
            ->with(['user', 'commentable'])
            ->latest()
            ->get();
    }

    /**
     * Get paginated pending comments.
     *
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Comment>
     */
    public function getPendingPaginated(int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->where('status', 'pending')
            ->with(['user', 'commentable'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get all reported comments.
     *
     * @return Collection<int, Comment>
     */
    public function getReported(): Collection
    {
        return $this->query()
            ->where('report_count', '>', 0)
            ->with(['user', 'commentable'])
            ->latest()
            ->get();
    }

    /**
     * Get paginated root comments for a specific model.
     *
     * @param string $type The commentable type
     * @param string $id The commentable ID
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Comment>
     */
    public function getForModel(string $type, string $id, int $perPage = 20): LengthAwarePaginator
    {
        return $this->query()
            ->where('commentable_type', $type)
            ->where('commentable_id', $id)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a comment including soft-deleted ones.
     *
     * @param string $id The comment ID
     *
     * @return Comment|null
     */
    public function findWithTrashed(string $id): ?Comment
    {
        return $this->model->withTrashed()
            ->with(['user', 'replies.user'])
            ->find($id);
    }

    /**
     * Get comment statistics for optional commentable entity.
     *
     * Optimized to use a single query with conditional aggregates
     * instead of multiple separate count queries.
     *
     * @param string|null $commentableType Filter by commentable type
     * @param string|null $commentableId Filter by commentable ID
     *
     * @return array{total: int, approved: int, pending: int, spam: int}
     */
    public function getStats(?string $commentableType = null, ?string $commentableId = null): array
    {
        $query = $this->query();
        
        if ($commentableType && $commentableId) {
            $query->where('commentable_type', $commentableType)
                  ->where('commentable_id', $commentableId);
        }
        
        // Single query with conditional counts for better performance
        $stats = $query->selectRaw("
            COUNT(*) as total,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN is_spam = 1 THEN 1 ELSE 0 END) as spam
        ")->first();

        return [
            'total' => (int) ($stats->total ?? 0),
            'approved' => (int) ($stats->approved ?? 0),
            'pending' => (int) ($stats->pending ?? 0),
            'spam' => (int) ($stats->spam ?? 0),
        ];
    }

    /**
     * Get comments by multiple IDs.
     *
     * @param array<string> $ids Array of comment IDs
     *
     * @return Collection<int, Comment>
     */
    public function findMany(array $ids): Collection
    {
        return $this->query()
            ->whereIn('id', $ids)
            ->with(['user'])
            ->get();
    }

    /**
     * Get spam comments older than specified days.
     *
     * @param int $daysOld Number of days
     *
     * @return Collection<int, Comment>
     */
    public function getOldSpam(int $daysOld = 30): Collection
    {
        return $this->query()
            ->where('is_spam', true)
            ->where('created_at', '<', now()->subDays($daysOld))
            ->get();
    }

    /**
     * Get comments by commentable for locking/unlocking.
     *
     * @param string $type The commentable type
     * @param string $id The commentable ID
     *
     * @return Collection<int, Comment>
     */
    public function getByCommentable(string $type, string $id): Collection
    {
        return $this->query()
            ->where('commentable_type', $type)
            ->where('commentable_id', $id)
            ->get();
    }

    /**
     * Build a filtered query based on provided filters.
     *
     * @param array<string, mixed> $filters
     *
     * @return Builder<Comment>
     */
    private function buildFilteredQuery(array $filters): Builder
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

        return $query;
    }
}
