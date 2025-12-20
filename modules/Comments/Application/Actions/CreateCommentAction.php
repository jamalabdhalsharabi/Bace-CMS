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
 *
 * Handles the creation of new comments on commentable entities.
 * Supports both authenticated user comments and guest comments,
 * with automatic or manual approval based on system configuration.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CreateCommentAction extends Action
{
    /**
     * Create a new CreateCommentAction instance.
     *
     * @param CommentRepository $repository The comment repository for data operations
     */
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    /**
     * Execute the comment creation action.
     *
     * Creates a new comment with the provided data. The comment status
     * is determined by the system configuration - it can be automatically
     * approved or set to pending for moderation.
     *
     * For authenticated users, the user_id is automatically set.
     * For guest users, author_name and author_email are used instead.
     *
     * @param CommentData $data The validated comment data transfer object
     * 
     * @return Comment The newly created comment with relationships loaded
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When parent comment not found
     * @throws \Exception When comment creation fails
     */
    public function execute(CommentData $data): Comment
    {
        $autoApprove = config('comments.moderation.auto_approve', false);

        $comment = $this->repository->create([
            'commentable_type' => $data->commentable_type,
            'commentable_id' => $data->commentable_id,
            'parent_id' => $data->parent_id,
            'user_id' => $data->user_id ?? $this->userId(),
            'author_name' => $data->author_name,
            'author_email' => $data->author_email,
            'content' => $data->content,
            'status' => $autoApprove ? 'approved' : 'pending',
        ]);

        event(new CommentCreated($comment));

        return $comment->fresh(['user', 'replies']);
    }
}
