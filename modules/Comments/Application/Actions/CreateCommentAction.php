<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\DTO\CommentData;
use Modules\Comments\Domain\Events\CommentCreated;
use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Create Comment Action.
 */
final class CreateCommentAction extends Action
{
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    public function execute(CommentData $data): Comment
    {
        $autoApprove = config('comments.auto_approve', false);

        $comment = $this->repository->create([
            'commentable_type' => $data->commentable_type,
            'commentable_id' => $data->commentable_id,
            'parent_id' => $data->parent_id,
            'user_id' => $data->user_id ?? $this->userId(),
            'guest_name' => $data->guest_name,
            'guest_email' => $data->guest_email,
            'content' => $data->content,
            'status' => $autoApprove ? 'approved' : 'pending',
        ]);

        event(new CommentCreated($comment));

        return $comment->fresh(['user', 'replies']);
    }
}
