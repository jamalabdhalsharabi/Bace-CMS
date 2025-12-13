<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;
use Modules\Core\Application\Actions\Action;

final class RejectCommentAction extends Action
{
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    public function execute(Comment $comment, ?string $reason = null): Comment
    {
        $meta = $comment->meta ?? [];
        if ($reason) {
            $meta['rejection_reason'] = $reason;
        }

        $this->repository->update($comment->id, [
            'status' => 'rejected',
            'meta' => $meta,
        ]);

        return $comment->fresh();
    }
}
