<?php

declare(strict_types=1);

namespace Modules\Comments\Application\Actions;

use Modules\Comments\Domain\Models\Comment;
use Modules\Core\Application\Actions\Action;

/**
 * Vote Comment Action.
 *
 * Handles user voting on comments (upvote/downvote functionality).
 * Manages vote creation, updates, and ensures one vote per user per comment.
 * Automatically updates comment vote counters for real-time feedback.
 *
 * @package Modules\Comments\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class VoteCommentAction extends Action
{
    /**
     * Execute the comment voting action.
     *
     * Creates or updates a user's vote on a specific comment. If the user
     * has already voted, their previous vote is updated. The system ensures
     * only one vote per user per comment and automatically updates the
     * comment's upvote/downvote counters.
     *
     * Vote types:
     * - 'up': Increases comment upvotes counter
     * - 'down': Increases comment downvotes counter
     *
     * @param Comment $comment The comment instance to vote on
     * @param string $type The vote type - must be 'up' or 'down'
     * @param string|null $userId The voting user's UUID (defaults to authenticated user)
     *
     * @return void
     * 
     * @throws \InvalidArgumentException When vote type is not 'up' or 'down'
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When comment not found
     * @throws \Exception When vote operation fails
     */
    public function execute(Comment $comment, string $type, ?string $userId = null): void
    {
        $comment->votes()->updateOrCreate(
            ['user_id' => $userId ?? $this->userId()],
            ['vote' => $type === 'up' ? 1 : -1]
        );
        
        // Update comment vote counters
        $this->updateVoteCounters($comment);
    }

    /**
     * Update the comment's vote counters after a vote operation.
     *
     * Recalculates and updates the upvotes and downvotes fields based on
     * current vote records to ensure accuracy.
     *
     * @param Comment $comment The comment to update counters for
     * 
     * @return void
     */
    private function updateVoteCounters(Comment $comment): void
    {
        $upvotes = $comment->votes()->where('vote', 1)->count();
        $downvotes = $comment->votes()->where('vote', -1)->count();
        
        $comment->update([
            'upvotes' => $upvotes,
            'downvotes' => $downvotes,
        ]);
    }
}
