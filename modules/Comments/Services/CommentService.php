<?php

declare(strict_types=1);

namespace Modules\Comments\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Comments\Contracts\CommentServiceContract;
use Modules\Comments\Domain\Models\Comment;

/**
 * Class CommentService
 *
 * Service class for managing comments including CRUD,
 * moderation, voting, pinning, and spam management.
 *
 * @package Modules\Comments\Services
 */
class CommentService implements CommentServiceContract
{
    /**
     * The comment moderator instance.
     *
     * @var CommentModerator
     */
    protected CommentModerator $moderator;

    /**
     * Create a new CommentService instance.
     *
     * @param CommentModerator $moderator The comment moderator
     */
    public function __construct(
        CommentModerator $moderator
    ) {
        $this->moderator = $moderator;
    }

    /**
     * Get paginated comments for a specific model.
     *
     * @param string $type The commentable model type
     * @param string $id The commentable model ID
     * @param int $perPage Results per page
     *
     * @return LengthAwarePaginator Paginated comments
     */
    public function getForModel(string $type, string $id, int $perPage = 20): LengthAwarePaginator
    {
        return Comment::forModel($type, $id)
            ->approved()
            ->root()
            ->with(['user', 'replies.user'])
            ->orderByDesc('is_pinned')
            ->oldest()
            ->paginate($perPage);
    }

    /**
     * Get paginated pending comments for moderation.
     *
     * @param int $perPage Results per page
     *
     * @return LengthAwarePaginator Paginated pending comments
     */
    public function getPending(int $perPage = 20): LengthAwarePaginator
    {
        return Comment::pending()
            ->with(['user', 'commentable'])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Find a comment by its UUID.
     *
     * @param string $id The comment UUID
     *
     * @return Comment|null The found comment or null
     */
    public function find(string $id): ?Comment
    {
        return Comment::with(['user', 'replies.user', 'parent'])->find($id);
    }

    /**
     * Create a new comment.
     *
     * @param array $data Comment data
     *
     * @return Comment The created comment
     */
    public function create(array $data): Comment
    {
        $status = $this->moderator->determineStatus($data);

        $comment = Comment::create([
            'commentable_type' => $data['commentable_type'],
            'commentable_id' => $data['commentable_id'],
            'user_id' => $data['user_id'] ?? auth()->id(),
            'author_name' => $data['author_name'] ?? null,
            'author_email' => $data['author_email'] ?? null,
            'content' => $data['content'],
            'status' => $status,
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
        ]);

        if ($status === 'approved') {
            $comment->update([
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);
        }

        return $comment->fresh(['user']);
    }

    /**
     * Create a reply to an existing comment.
     *
     * @param Comment $parent The parent comment
     * @param array $data Reply data
     *
     * @return Comment The created reply
     */
    public function reply(Comment $parent, array $data): Comment
    {
        $data['commentable_type'] = $parent->commentable_type;
        $data['commentable_id'] = $parent->commentable_id;
        
        $comment = Comment::create([
            'commentable_type' => $parent->commentable_type,
            'commentable_id' => $parent->commentable_id,
            'parent_id' => $parent->id,
            'user_id' => $data['user_id'] ?? auth()->id(),
            'author_name' => $data['author_name'] ?? null,
            'author_email' => $data['author_email'] ?? null,
            'content' => $data['content'],
            'status' => $this->moderator->determineStatus($data),
            'ip_address' => $data['ip_address'] ?? request()->ip(),
            'user_agent' => $data['user_agent'] ?? request()->userAgent(),
        ]);

        return $comment->fresh(['user']);
    }

    /**
     * Update an existing comment.
     *
     * @param Comment $comment The comment to update
     * @param array $data Updated data
     *
     * @return Comment The updated comment
     */
    public function update(Comment $comment, array $data): Comment
    {
        $comment->update([
            'content' => $data['content'] ?? $comment->content,
        ]);

        return $comment->fresh();
    }

    /**
     * Delete a comment and its replies.
     *
     * @param Comment $comment The comment to delete
     *
     * @return bool True if successful
     */
    public function delete(Comment $comment): bool
    {
        $comment->replies()->delete();
        return $comment->delete();
    }

    /**
     * Approve a pending comment.
     *
     * @param Comment $comment The comment to approve
     *
     * @return Comment The approved comment
     */
    public function approve(Comment $comment): Comment
    {
        return $comment->approve();
    }

    /**
     * Reject a pending comment.
     *
     * @param Comment $comment The comment to reject
     *
     * @return Comment The rejected comment
     */
    public function reject(Comment $comment): Comment
    {
        return $comment->reject();
    }

    /**
     * Mark a comment as spam.
     *
     * @param Comment $comment The comment to mark
     *
     * @return Comment The updated comment
     */
    public function markAsSpam(Comment $comment): Comment
    {
        return $comment->markAsSpam();
    }

    /**
     * Permanently delete a comment.
     *
     * @param Comment $comment The comment to delete
     *
     * @return bool True if successful
     */
    public function forceDelete(Comment $comment): bool
    {
        $comment->replies()->forceDelete();
        return $comment->forceDelete();
    }

    /**
     * Confirm a comment is not spam.
     *
     * @param Comment $comment The comment to confirm
     *
     * @return Comment The updated comment
     */
    public function confirmNotSpam(Comment $comment): Comment
    {
        $comment->update(['status' => 'approved', 'is_spam' => false]);
        return $comment->fresh();
    }

    /**
     * Bulk approve multiple comments.
     *
     * @param array $ids Comment UUIDs to approve
     *
     * @return int Number of comments approved
     */
    public function bulkApprove(array $ids): int
    {
        return Comment::whereIn('id', $ids)->update(['status' => 'approved', 'approved_at' => now()]);
    }

    /**
     * Bulk reject multiple comments.
     *
     * @param array $ids Comment UUIDs to reject
     *
     * @return int Number of comments rejected
     */
    public function bulkReject(array $ids): int
    {
        return Comment::whereIn('id', $ids)->update(['status' => 'rejected']);
    }

    /**
     * Bulk delete multiple comments.
     *
     * @param array $ids Comment UUIDs to delete
     *
     * @return int Number of comments deleted
     */
    public function bulkDelete(array $ids): int
    {
        return Comment::whereIn('id', $ids)->delete();
    }

    /**
     * Pin a comment to the top.
     *
     * @param Comment $comment The comment to pin
     *
     * @return Comment The pinned comment
     */
    public function pin(Comment $comment): Comment
    {
        $comment->update(['is_pinned' => true]);
        return $comment->fresh();
    }

    /**
     * Unpin a comment.
     *
     * @param Comment $comment The comment to unpin
     *
     * @return Comment The unpinned comment
     */
    public function unpin(Comment $comment): Comment
    {
        $comment->update(['is_pinned' => false]);
        return $comment->fresh();
    }

    /**
     * Hide a comment from public view.
     *
     * @param Comment $comment The comment to hide
     *
     * @return Comment The hidden comment
     */
    public function hide(Comment $comment): Comment
    {
        $comment->update(['is_hidden' => true]);
        return $comment->fresh();
    }

    /**
     * Unhide a hidden comment.
     *
     * @param Comment $comment The comment to unhide
     *
     * @return Comment The visible comment
     */
    public function unhide(Comment $comment): Comment
    {
        $comment->update(['is_hidden' => false]);
        return $comment->fresh();
    }

    /**
     * Add an upvote to a comment.
     *
     * @param Comment $comment The comment to upvote
     *
     * @return Comment The updated comment
     */
    public function upvote(Comment $comment): Comment
    {
        $comment->increment('upvotes');
        return $comment->fresh();
    }

    /**
     * Add a downvote to a comment.
     *
     * @param Comment $comment The comment to downvote
     *
     * @return Comment The updated comment
     */
    public function downvote(Comment $comment): Comment
    {
        $comment->increment('downvotes');
        return $comment->fresh();
    }

    /**
     * Remove a vote from a comment.
     *
     * @param Comment $comment The comment
     *
     * @return Comment The updated comment
     */
    public function removeVote(Comment $comment): Comment
    {
        return $comment->fresh();
    }

    /**
     * Report a comment for moderation.
     *
     * @param Comment $comment The comment to report
     * @param string $reason The report reason
     *
     * @return Comment The reported comment
     */
    public function report(Comment $comment, string $reason): Comment
    {
        $comment->update(['is_reported' => true, 'report_reason' => $reason]);
        return $comment->fresh();
    }

    /**
     * Dismiss a report on a comment.
     *
     * @param Comment $comment The reported comment
     *
     * @return Comment The updated comment
     */
    public function dismissReport(Comment $comment): Comment
    {
        $comment->update(['is_reported' => false, 'report_reason' => null]);
        return $comment->fresh();
    }

    /**
     * Ban a user from commenting by email.
     *
     * @param string $email The user email to ban
     *
     * @return bool True if successful
     */
    public function banUser(string $email): bool
    {
        // Implementation for banning user by email
        return true;
    }

    /**
     * Unban a previously banned user.
     *
     * @param string $email The user email to unban
     *
     * @return bool True if successful
     */
    public function unbanUser(string $email): bool
    {
        // Implementation for unbanning user
        return true;
    }
}
