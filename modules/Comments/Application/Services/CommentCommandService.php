<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Services;

use Modules\Comments\Application\Actions\ApproveCommentAction;
use Modules\Comments\Application\Actions\CreateCommentAction;
use Modules\Comments\Application\Actions\DeleteCommentAction;
use Modules\Comments\Application\Actions\RejectCommentAction;
use Modules\Comments\Application\Actions\ReportCommentAction;
use Modules\Comments\Application\Actions\SpamCommentAction;
use Modules\Comments\Application\Actions\UpdateCommentAction;
use Modules\Comments\Domain\DTO\CommentData;
use Modules\Comments\Domain\Models\Comment;

/**
 * Comment Command Service.
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
        return Comment::whereIn('id', $ids)->update(['status' => 'approved']);
    }

    public function bulkDelete(array $ids): int
    {
        return Comment::whereIn('id', $ids)->delete();
    }
}
