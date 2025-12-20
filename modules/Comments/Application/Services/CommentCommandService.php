<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Services;

use Modules\Comments\Application\Actions\ApproveCommentAction;
use Modules\Comments\Application\Actions\BulkApproveCommentsAction;
use Modules\Comments\Application\Actions\BulkDeleteCommentsAction;
use Modules\Comments\Application\Actions\BulkRejectCommentsAction;
use Modules\Comments\Application\Actions\CleanSpamCommentsAction;
use Modules\Comments\Application\Actions\CreateCommentAction;
use Modules\Comments\Application\Actions\DeleteCommentAction;
use Modules\Comments\Application\Actions\ForceDeleteCommentAction;
use Modules\Comments\Application\Actions\HideCommentAction;
use Modules\Comments\Application\Actions\LockCommentsAction;
use Modules\Comments\Application\Actions\MarkAsNotSpamAction;
use Modules\Comments\Application\Actions\PinCommentAction;
use Modules\Comments\Application\Actions\RejectCommentAction;
use Modules\Comments\Application\Actions\ReplyCommentAction;
use Modules\Comments\Application\Actions\ReportCommentAction;
use Modules\Comments\Application\Actions\SpamCommentAction;
use Modules\Comments\Application\Actions\UnhideCommentAction;
use Modules\Comments\Application\Actions\UnlockCommentsAction;
use Modules\Comments\Application\Actions\UnpinCommentAction;
use Modules\Comments\Application\Actions\UpdateCommentAction;
use Modules\Comments\Application\Actions\VoteCommentAction;
use Modules\Comments\Domain\DTO\CommentData;
use Modules\Comments\Domain\Models\Comment;

/**
 * Comment Command Service.
 *
 * Orchestrates all write operations for comments via Action classes.
 * Provides a clean interface for executing comment-related commands
 * while maintaining separation of concerns and single responsibility.
 *
 * This service follows CQRS pattern by handling only write operations.
 * All read operations are handled by CommentQueryService.
 *
 * Key responsibilities:
 * - Delegates to Action classes for business logic execution
 * - Maintains transaction boundaries where needed
 * - Provides consistent interface for controllers
 * - No direct database access - all through Actions
 *
 * @package Modules\Comments\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CommentCommandService
{
    public function __construct(
        private readonly CreateCommentAction $createAction,
        private readonly UpdateCommentAction $updateAction,
        private readonly DeleteCommentAction $deleteAction,
        private readonly ApproveCommentAction $approveAction,
        private readonly RejectCommentAction $rejectAction,
        private readonly SpamCommentAction $spamAction,
        private readonly ReportCommentAction $reportAction,
        private readonly ReplyCommentAction $replyAction,
        private readonly HideCommentAction $hideAction,
        private readonly UnhideCommentAction $unhideAction,
        private readonly PinCommentAction $pinAction,
        private readonly UnpinCommentAction $unpinAction,
        private readonly VoteCommentAction $voteAction,
        private readonly ForceDeleteCommentAction $forceDeleteAction,
        private readonly LockCommentsAction $lockAction,
        private readonly UnlockCommentsAction $unlockAction,
        private readonly BulkApproveCommentsAction $bulkApproveAction,
        private readonly BulkRejectCommentsAction $bulkRejectAction,
        private readonly BulkDeleteCommentsAction $bulkDeleteAction,
        private readonly CleanSpamCommentsAction $cleanSpamAction,
        private readonly MarkAsNotSpamAction $markAsNotSpamAction,
    ) {}

    /**
     * Create a new comment.
     *
     * @param CommentData $data The comment data transfer object
     *
     * @return Comment The created comment instance
     */
    public function create(CommentData $data): Comment
    {
        return $this->createAction->execute($data);
    }

    /**
     * Update an existing comment.
     *
     * @param Comment $comment The comment to update
     * @param array<string, mixed> $data The update data
     *
     * @return Comment The updated comment instance
     */
    public function update(Comment $comment, array $data): Comment
    {
        return $this->updateAction->execute($comment, $data);
    }

    /**
     * Soft delete a comment.
     *
     * @param Comment $comment The comment to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(Comment $comment): bool
    {
        return $this->deleteAction->execute($comment);
    }

    /**
     * Approve a pending comment.
     *
     * @param Comment $comment The comment to approve
     *
     * @return Comment The approved comment instance
     */
    public function approve(Comment $comment): Comment
    {
        return $this->approveAction->execute($comment);
    }

    /**
     * Reject a pending comment.
     *
     * @param Comment $comment The comment to reject
     * @param string|null $reason Optional rejection reason
     *
     * @return Comment The rejected comment instance
     */
    public function reject(Comment $comment, ?string $reason = null): Comment
    {
        return $this->rejectAction->execute($comment, $reason);
    }

    /**
     * Mark a comment as spam.
     *
     * @param Comment $comment The comment to mark as spam
     *
     * @return Comment The spam-marked comment instance
     */
    public function spam(Comment $comment): Comment
    {
        return $this->spamAction->execute($comment);
    }

    public function report(Comment $comment, string $reason, ?string $details = null, ?string $userId = null)
    {
        return $this->reportAction->execute($comment, $reason, $details, $userId);
    }

    /**
     * Bulk approve multiple comments.
     *
     * @param array<string> $ids Array of comment UUIDs
     *
     * @return int Number of comments approved
     */
    public function bulkApprove(array $ids): int
    {
        return $this->bulkApproveAction->execute($ids);
    }

    /**
     * Bulk reject multiple comments.
     *
     * @param array<string> $ids Array of comment UUIDs
     *
     * @return int Number of comments rejected
     */
    public function bulkReject(array $ids): int
    {
        return $this->bulkRejectAction->execute($ids);
    }

    /**
     * Bulk delete multiple comments.
     *
     * @param array<string> $ids Array of comment UUIDs
     *
     * @return int Number of comments deleted
     */
    public function bulkDelete(array $ids): int
    {
        return $this->bulkDeleteAction->execute($ids);
    }

    /**
     * Create a reply to an existing comment.
     *
     * @param Comment $parent The parent comment
     * @param array<string, mixed> $data The reply data
     *
     * @return Comment The created reply comment instance
     */
    public function reply(Comment $parent, array $data): Comment
    {
        return $this->replyAction->execute($parent, $data);
    }

    /**
     * Mark a comment as spam (alias for spam method).
     *
     * @param Comment $comment The comment to mark as spam
     *
     * @return Comment The spam-marked comment instance
     */
    public function markAsSpam(Comment $comment): Comment
    {
        return $this->spamAction->execute($comment);
    }

    /**
     * Remove spam marking from a comment.
     *
     * @param Comment $comment The comment to unmark as spam
     *
     * @return Comment The unmarked comment instance
     */
    public function markAsNotSpam(Comment $comment): Comment
    {
        return $this->markAsNotSpamAction->execute($comment);
    }

    /**
     * Hide a comment from public view.
     *
     * @param Comment $comment The comment to hide
     *
     * @return Comment The hidden comment instance
     */
    public function hide(Comment $comment): Comment
    {
        return $this->hideAction->execute($comment);
    }

    /**
     * Unhide a previously hidden comment.
     *
     * @param Comment $comment The comment to unhide
     *
     * @return Comment The unhidden comment instance
     */
    public function unhide(Comment $comment): Comment
    {
        return $this->unhideAction->execute($comment);
    }

    /**
     * Pin a comment to the top.
     *
     * @param Comment $comment The comment to pin
     *
     * @return Comment The pinned comment instance
     */
    public function pin(Comment $comment): Comment
    {
        return $this->pinAction->execute($comment);
    }

    /**
     * Unpin a pinned comment.
     *
     * @param Comment $comment The comment to unpin
     *
     * @return Comment The unpinned comment instance
     */
    public function unpin(Comment $comment): Comment
    {
        return $this->unpinAction->execute($comment);
    }

    /**
     * Vote on a comment (upvote or downvote).
     *
     * @param Comment $comment The comment to vote on
     * @param string $type The vote type ('up' or 'down')
     * @param string|null $userId Optional user ID (defaults to authenticated user)
     *
     * @return void
     */
    public function vote(Comment $comment, string $type, ?string $userId = null): void
    {
        $this->voteAction->execute($comment, $type, $userId);
    }

    /**
     * Permanently delete a comment.
     *
     * @param Comment $comment The comment to permanently delete
     *
     * @return bool True if deletion was successful
     */
    public function forceDelete(Comment $comment): bool
    {
        return $this->forceDeleteAction->execute($comment);
    }

    /**
     * Lock all comments for a commentable entity.
     *
     * @param string $modelType The commentable model type
     * @param string $modelId The commentable model ID
     *
     * @return void
     */
    public function lockComments(string $modelType, string $modelId): void
    {
        $this->lockAction->execute($modelType, $modelId);
    }

    /**
     * Unlock all comments for a commentable entity.
     *
     * @param string $modelType The commentable model type
     * @param string $modelId The commentable model ID
     *
     * @return void
     */
    public function unlockComments(string $modelType, string $modelId): void
    {
        $this->unlockAction->execute($modelType, $modelId);
    }

    /**
     * Ban a user from commenting.
     *
     * @param string $userId The user ID to ban
     * @param string|null $reason Optional ban reason
     * @param int|null $duration Optional ban duration in days
     *
     * @return void
     * @todo Implement ban system integration
     */
    public function banUser(string $userId, ?string $reason = null, ?int $duration = null): void
    {
        // Implementation depends on your ban system
    }

    /**
     * Unban a user from commenting.
     *
     * @param string $userId The user ID to unban
     *
     * @return void
     * @todo Implement ban system integration
     */
    public function unbanUser(string $userId): void
    {
        // Implementation depends on your ban system
    }

    /**
     * Clean old spam comments.
     *
     * @return int Number of spam comments cleaned
     */
    public function cleanSpam(): int
    {
        return $this->cleanSpamAction->execute();
    }
}
