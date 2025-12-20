<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Events\CommentCreated;
use Modules\Comments\Domain\Models\Comment;
use Modules\Comments\Domain\Repositories\CommentRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Reply Comment Action.
 *
 * Handles the creation of reply comments to existing comments.
 * Maintains proper threading hierarchy by setting parent relationships
 * and inheriting commentable entity information from the parent comment.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ReplyCommentAction extends Action
{
    /**
     * Create a new ReplyCommentAction instance.
     *
     * @param CommentRepository $repository The comment repository for data operations
     */
    public function __construct(
        private readonly CommentRepository $repository
    ) {}

    /**
     * Execute the comment reply creation action.
     *
     * Creates a new reply comment attached to a parent comment. The reply
     * inherits the commentable type and ID from the parent to maintain
     * proper threading. Reply status is determined by system configuration.
     *
     * The reply automatically:
     * - Links to the parent comment via parent_id
     * - Inherits commentable_type and commentable_id from parent
     * - Sets appropriate approval status based on configuration
     * - Triggers CommentCreated event for system notifications
     *
     * @param Comment $parent The parent comment to reply to
     * @param array<string, mixed> $data Reply data containing content and optional user info
     * 
     * @return Comment The newly created reply comment with relationships loaded
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When parent comment not found
     * @throws \Exception When reply creation fails
     */
    public function execute(Comment $parent, array $data): Comment
    {
        $autoApprove = config('comments.moderation.auto_approve', false);

        $comment = $this->repository->create([
            'commentable_type' => $parent->commentable_type,
            'commentable_id' => $parent->commentable_id,
            'parent_id' => $parent->id,
            'user_id' => $data['user_id'] ?? $this->userId(),
            'author_name' => $data['author_name'] ?? null,
            'author_email' => $data['author_email'] ?? null,
            'content' => $data['content'],
            'status' => $autoApprove ? 'approved' : 'pending',
            'depth' => ($parent->depth ?? 0) + 1,
        ]);

        event(new CommentCreated($comment));

        return $comment->fresh(['user', 'replies']);
    }
}
