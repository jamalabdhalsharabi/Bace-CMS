<?php

declare(strict_types=1);

namespace Modules\Comments\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Comments\Domain\Models\Comment;

/**
 * Interface CommentServiceContract
 * 
 * Defines the contract for comment management services.
 * Handles CRUD, replies, moderation, voting, reporting,
 * pinning, hiding, and user banning.
 * 
 * @package Modules\Comments\Contracts
 */
interface CommentServiceContract
{
    /** @param string $type Commentable type @param string $id Commentable ID @param int $perPage @return LengthAwarePaginator */
    public function getForModel(string $type, string $id, int $perPage = 20): LengthAwarePaginator;

    /** @param int $perPage @return LengthAwarePaginator */
    public function getPending(int $perPage = 20): LengthAwarePaginator;

    /** @param string $id Comment UUID @return Comment|null */
    public function find(string $id): ?Comment;

    /** @param array $data @return Comment */
    public function create(array $data): Comment;

    /** @param Comment $parent @param array $data @return Comment */
    public function reply(Comment $parent, array $data): Comment;

    /** @param Comment $comment @param array $data @return Comment */
    public function update(Comment $comment, array $data): Comment;

    /** @param Comment $comment @return bool */
    public function delete(Comment $comment): bool;

    /** @param Comment $comment @return bool */
    public function forceDelete(Comment $comment): bool;

    /** @param Comment $comment @return Comment */
    public function approve(Comment $comment): Comment;

    /** @param Comment $comment @return Comment */
    public function reject(Comment $comment): Comment;

    /** @param Comment $comment @return Comment */
    public function markAsSpam(Comment $comment): Comment;

    /** @param Comment $comment @return Comment */
    public function confirmNotSpam(Comment $comment): Comment;

    /** @param array $ids @return int */
    public function bulkApprove(array $ids): int;

    /** @param array $ids @return int */
    public function bulkReject(array $ids): int;

    /** @param array $ids @return int */
    public function bulkDelete(array $ids): int;

    /** @param Comment $comment @return Comment */
    public function pin(Comment $comment): Comment;

    /** @param Comment $comment @return Comment */
    public function unpin(Comment $comment): Comment;

    /** @param Comment $comment @return Comment */
    public function hide(Comment $comment): Comment;

    /** @param Comment $comment @return Comment */
    public function unhide(Comment $comment): Comment;

    /** @param Comment $comment @return Comment */
    public function upvote(Comment $comment): Comment;

    /** @param Comment $comment @return Comment */
    public function downvote(Comment $comment): Comment;

    /** @param Comment $comment @return Comment */
    public function removeVote(Comment $comment): Comment;

    /** @param Comment $comment @param string $reason @return Comment */
    public function report(Comment $comment, string $reason): Comment;

    /** @param Comment $comment @return Comment */
    public function dismissReport(Comment $comment): Comment;

    /** @param string $email @return bool */
    public function banUser(string $email): bool;

    /** @param string $email @return bool */
    public function unbanUser(string $email): bool;
}
