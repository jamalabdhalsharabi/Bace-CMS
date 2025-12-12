<?php

declare(strict_types=1);

namespace Modules\Comments\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Comments\Domain\Models\Comment;

interface CommentServiceContract
{
    // CRUD
    public function getForModel(string $type, string $id, int $perPage = 20): LengthAwarePaginator;
    public function getPending(int $perPage = 20): LengthAwarePaginator;
    public function find(string $id): ?Comment;
    public function create(array $data): Comment;
    public function reply(Comment $parent, array $data): Comment;
    public function update(Comment $comment, array $data): Comment;
    public function delete(Comment $comment): bool;
    public function forceDelete(Comment $comment): bool;

    // Moderation
    public function approve(Comment $comment): Comment;
    public function reject(Comment $comment): Comment;
    public function markAsSpam(Comment $comment): Comment;
    public function confirmNotSpam(Comment $comment): Comment;
    public function bulkApprove(array $ids): int;
    public function bulkReject(array $ids): int;
    public function bulkDelete(array $ids): int;

    // Features
    public function pin(Comment $comment): Comment;
    public function unpin(Comment $comment): Comment;
    public function hide(Comment $comment): Comment;
    public function unhide(Comment $comment): Comment;

    // Voting
    public function upvote(Comment $comment): Comment;
    public function downvote(Comment $comment): Comment;
    public function removeVote(Comment $comment): Comment;

    // Reporting
    public function report(Comment $comment, string $reason): Comment;
    public function dismissReport(Comment $comment): Comment;

    // User
    public function banUser(string $email): bool;
    public function unbanUser(string $email): bool;
}
