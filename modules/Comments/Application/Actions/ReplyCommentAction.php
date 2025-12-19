<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Events\CommentCreated;
use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;
use Modules\Core\Application\Actions\Action;

final class ReplyCommentAction extends Action
{
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    public function execute(Comment $parent, array $data): Comment
    {
        $autoApprove = config('comments.auto_approve', false);

        $comment = $this->repository->create([
            'commentable_type' => $parent->commentable_type,
            'commentable_id' => $parent->commentable_id,
            'parent_id' => $parent->id,
            'user_id' => $data['user_id'] ?? $this->userId(),
            'content' => $data['content'],
            'status' => $autoApprove ? 'approved' : 'pending',
        ]);

        event(new CommentCreated($comment));

        return $comment->fresh(['user', 'replies']);
    }
}
