<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;

/**
 * Comment Query Service.
 *
 * Handles all read-only query operations for comments.
 * Provides a clean interface for retrieving comment data with proper
 * filtering, pagination, and relationship loading.
 *
 * This service follows CQRS pattern by separating queries from commands.
 * All write operations are handled by CommentCommandService.
 *
 * @package Modules\Comments\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CommentQueryService
{
    /**
     * Create a new CommentQueryService instance.
     *
     * @param CommentRepository $repository The comment repository for data access
     */
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    /**
     * Get paginated list of comments with optional filters.
     *
     * @param array<string, mixed> $filters Optional filters (commentable_type, commentable_id, status, user_id, root)
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Comment> Paginated comment collection
     */
    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository
            ->with(['user', 'commentable'])
            ->getPaginated($filters, $perPage);
    }

    /**
     * Find a comment by ID with relationships.
     *
     * @param string $id The comment UUID
     *
     * @return Comment|null The comment instance or null if not found
     */
    public function find(string $id): ?Comment
    {
        return $this->repository
            ->with(['user', 'replies.user', 'commentable'])
            ->find($id);
    }

    /**
     * Get approved comments for a specific commentable entity.
     *
     * @param string $type The commentable model type
     * @param string $id The commentable model ID
     *
     * @return Collection<int, Comment> Collection of approved comments
     */
    public function getForCommentable(string $type, string $id): Collection
    {
        return $this->repository->getForCommentable($type, $id);
    }

    /**
     * Get paginated pending comments for moderation.
     *
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Comment> Paginated pending comments
     */
    public function getPending(int $perPage = 20): LengthAwarePaginator
    {
        return Comment::where('status', 'pending')
            ->with(['user', 'commentable'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Get all reported comments.
     *
     * @return Collection<int, Comment> Collection of reported comments
     */
    public function getReported(): Collection
    {
        return $this->repository->getReported();
    }

    /**
     * Get paginated comments for a specific model.
     *
     * @param string $type The commentable model type
     * @param string $id The commentable model ID
     * @param int $perPage Number of items per page
     *
     * @return LengthAwarePaginator<Comment> Paginated comments
     */
    public function getForModel(string $type, string $id, int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository->getForModel($type, $id, $perPage);
    }

    /**
     * Find a comment including soft-deleted ones.
     *
     * @param string $id The comment UUID
     *
     * @return Comment|null The comment instance or null if not found
     */
    public function findWithTrashed(string $id): ?Comment
    {
        return $this->repository->findWithTrashed($id);
    }

    /**
     * Get comment statistics.
     *
     * @param string|null $commentableType Optional filter by commentable type
     * @param string|null $commentableId Optional filter by commentable ID
     *
     * @return array{total: int, approved: int, pending: int, spam: int} Statistics array
     */
    public function getStats(?string $commentableType = null, ?string $commentableId = null): array
    {
        return $this->repository->getStats($commentableType, $commentableId);
    }
}
