<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Vote Comment Action.
 *
 * Records a user's vote (upvote/downvote) on a comment.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class VoteCommentAction extends Action
{
    /**
     * Execute the vote action.
     *
     * @param Comment $comment The comment to vote on
     * @param string $type The vote type ('up' or 'down')
     * @param string|null $userId The user ID (defaults to authenticated user)
     *
     * @return void
     */
    public function execute(Comment $comment, string $type, ?string $userId = null): void
    {
        $comment->votes()->updateOrCreate(
            ['user_id' => $userId ?? $this->userId()],
            ['type' => $type]
        );
    }
}
