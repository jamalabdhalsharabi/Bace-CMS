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
 * Orchestrates all write operations via Action classes.
 * No direct Repository or Model usage - delegates to Actions only.
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

    public function create(CommentData $data): Comment
    {
        return $this->createAction->execute($data);
    }

    public function update(Comment $comment, array $data): Comment
    {
        return $this->updateAction->execute($comment, $data);
    }

    public function delete(Comment $comment): bool
    {
        return $this->deleteAction->execute($comment);
    }

    public function approve(Comment $comment): Comment
    {
        return $this->approveAction->execute($comment);
    }

    public function reject(Comment $comment, ?string $reason = null): Comment
    {
        return $this->rejectAction->execute($comment, $reason);
    }

    public function spam(Comment $comment): Comment
    {
        return $this->spamAction->execute($comment);
    }

    public function report(Comment $comment, ?string $reason = null): Comment
    {
        return $this->reportAction->execute($comment, $reason);
    }

    public function bulkApprove(array $ids): int
    {
        return $this->bulkApproveAction->execute($ids);
    }

    public function bulkReject(array $ids): int
    {
        return $this->bulkRejectAction->execute($ids);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->bulkDeleteAction->execute($ids);
    }

    public function reply(Comment $parent, array $data): Comment
    {
        return $this->replyAction->execute($parent, $data);
    }

    public function markAsSpam(Comment $comment): Comment
    {
        return $this->spamAction->execute($comment);
    }

    public function markAsNotSpam(Comment $comment): Comment
    {
        return $this->markAsNotSpamAction->execute($comment);
    }

    public function hide(Comment $comment): Comment
    {
        return $this->hideAction->execute($comment);
    }

    public function unhide(Comment $comment): Comment
    {
        return $this->unhideAction->execute($comment);
    }

    public function pin(Comment $comment): Comment
    {
        return $this->pinAction->execute($comment);
    }

    public function unpin(Comment $comment): Comment
    {
        return $this->unpinAction->execute($comment);
    }

    public function vote(Comment $comment, string $type, ?string $userId = null): void
    {
        $this->voteAction->execute($comment, $type, $userId);
    }

    public function forceDelete(Comment $comment): bool
    {
        return $this->forceDeleteAction->execute($comment);
    }

    public function lockComments(string $modelType, string $modelId): void
    {
        $this->lockAction->execute($modelType, $modelId);
    }

    public function unlockComments(string $modelType, string $modelId): void
    {
        $this->unlockAction->execute($modelType, $modelId);
    }

    public function banUser(string $userId, ?string $reason = null, ?int $duration = null): void
    {
        // Implementation depends on your ban system
    }

    public function unbanUser(string $userId): void
    {
        // Implementation depends on your ban system
    }

    public function cleanSpam(): int
    {
        return $this->cleanSpamAction->execute();
    }
}
