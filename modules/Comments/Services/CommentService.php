<?php

declare(strict_types=1);

namespace Modules\Comments\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Comments\Contracts\CommentServiceContract;
use Modules\Comments\Domain\Models\Comment;

class CommentService implements CommentServiceContract
{
    public function __construct(
        protected CommentModerator $moderator
    ) {}

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

    public function getPending(int $perPage = 20): LengthAwarePaginator
    {
        return Comment::pending()
            ->with(['user', 'commentable'])
            ->latest()
            ->paginate($perPage);
    }

    public function find(string $id): ?Comment
    {
        return Comment::with(['user', 'replies.user', 'parent'])->find($id);
    }

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

    public function update(Comment $comment, array $data): Comment
    {
        $comment->update([
            'content' => $data['content'] ?? $comment->content,
        ]);

        return $comment->fresh();
    }

    public function delete(Comment $comment): bool
    {
        $comment->replies()->delete();
        return $comment->delete();
    }

    public function approve(Comment $comment): Comment
    {
        return $comment->approve();
    }

    public function reject(Comment $comment): Comment
    {
        return $comment->reject();
    }

    public function markAsSpam(Comment $comment): Comment
    {
        return $comment->markAsSpam();
    }

    public function forceDelete(Comment $comment): bool
    {
        $comment->replies()->forceDelete();
        return $comment->forceDelete();
    }

    public function confirmNotSpam(Comment $comment): Comment
    {
        $comment->update(['status' => 'approved', 'is_spam' => false]);
        return $comment->fresh();
    }

    public function bulkApprove(array $ids): int
    {
        return Comment::whereIn('id', $ids)->update(['status' => 'approved', 'approved_at' => now()]);
    }

    public function bulkReject(array $ids): int
    {
        return Comment::whereIn('id', $ids)->update(['status' => 'rejected']);
    }

    public function bulkDelete(array $ids): int
    {
        return Comment::whereIn('id', $ids)->delete();
    }

    public function pin(Comment $comment): Comment
    {
        $comment->update(['is_pinned' => true]);
        return $comment->fresh();
    }

    public function unpin(Comment $comment): Comment
    {
        $comment->update(['is_pinned' => false]);
        return $comment->fresh();
    }

    public function hide(Comment $comment): Comment
    {
        $comment->update(['is_hidden' => true]);
        return $comment->fresh();
    }

    public function unhide(Comment $comment): Comment
    {
        $comment->update(['is_hidden' => false]);
        return $comment->fresh();
    }

    public function upvote(Comment $comment): Comment
    {
        $comment->increment('upvotes');
        return $comment->fresh();
    }

    public function downvote(Comment $comment): Comment
    {
        $comment->increment('downvotes');
        return $comment->fresh();
    }

    public function removeVote(Comment $comment): Comment
    {
        return $comment->fresh();
    }

    public function report(Comment $comment, string $reason): Comment
    {
        $comment->update(['is_reported' => true, 'report_reason' => $reason]);
        return $comment->fresh();
    }

    public function dismissReport(Comment $comment): Comment
    {
        $comment->update(['is_reported' => false, 'report_reason' => null]);
        return $comment->fresh();
    }

    public function banUser(string $email): bool
    {
        // Implementation for banning user by email
        return true;
    }

    public function unbanUser(string $email): bool
    {
        // Implementation for unbanning user
        return true;
    }
}
