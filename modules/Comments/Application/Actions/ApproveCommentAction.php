<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Events\CommentApproved;
use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;
use Modules\Core\Application\Actions\Action;

final class ApproveCommentAction extends Action
{
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    public function execute(Comment $comment): Comment
    {
        $this->repository->update($comment->id, ['status' => 'approved']);

        event(new CommentApproved($comment));

        return $comment->fresh();
    }
}
