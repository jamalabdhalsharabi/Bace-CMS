<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;

/**
 * Comment Query Service.
 */
final class CommentQueryService
{
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    public function list(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return $this->repository
            ->with(['user', 'commentable'])
            ->getPaginated($filters, $perPage);
    }

    public function find(string $id): ?Comment
    {
        return $this->repository
            ->with(['user', 'replies.user', 'commentable'])
            ->find($id);
    }

    public function getForCommentable(string $type, string $id): Collection
    {
        return $this->repository->getForCommentable($type, $id);
    }

    public function getPending(): Collection
    {
        return $this->repository->getPending();
    }

    public function getReported(): Collection
    {
        return $this->repository->getReported();
    }
}
