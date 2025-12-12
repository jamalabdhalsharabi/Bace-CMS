<?php

declare(strict_types=1);

namespace Modules\Comments\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Modules\Comments\Domain\Models\Comment;

trait HasComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    public function approvedComments(): MorphMany
    {
        return $this->comments()->approved()->root()->with('replies.user')->oldest();
    }

    public function pendingComments(): MorphMany
    {
        return $this->comments()->pending();
    }

    public function getCommentsCount(): int
    {
        return $this->comments()->approved()->count();
    }

    public function addComment(string $content, ?string $userId = null, array $guestInfo = []): Comment
    {
        return $this->comments()->create([
            'user_id' => $userId,
            'author_name' => $guestInfo['name'] ?? null,
            'author_email' => $guestInfo['email'] ?? null,
            'content' => $content,
            'status' => $this->shouldAutoApprove($userId) ? 'approved' : 'pending',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    protected function shouldAutoApprove(?string $userId): bool
    {
        if (!$userId) {
            return false;
        }

        if (config('comments.moderation.auto_approve', false)) {
            return true;
        }

        if (config('comments.moderation.auto_approve_verified_users', true)) {
            $user = config('auth.providers.users.model')::find($userId);
            return $user?->hasVerifiedEmail() ?? false;
        }

        return false;
    }
}
