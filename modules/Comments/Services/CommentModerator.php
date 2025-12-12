<?php

declare(strict_types=1);

namespace Modules\Comments\Services;

class CommentModerator
{
    protected array $spamKeywords = [
        'viagra', 'casino', 'lottery', 'winner', 'bitcoin',
        'cryptocurrency', 'click here', 'buy now', 'free money',
    ];

    public function determineStatus(array $data): string
    {
        if ($this->isSpam($data)) {
            return 'spam';
        }

        if ($this->shouldAutoApprove($data)) {
            return 'approved';
        }

        return 'pending';
    }

    public function isSpam(array $data): bool
    {
        if (!config('comments.spam_detection', true)) {
            return false;
        }

        $content = strtolower($data['content'] ?? '');

        foreach ($this->spamKeywords as $keyword) {
            if (str_contains($content, $keyword)) {
                return true;
            }
        }

        $linkCount = preg_match_all('/https?:\/\//', $content);
        if ($linkCount > 2) {
            return true;
        }

        return false;
    }

    protected function shouldAutoApprove(array $data): bool
    {
        if (config('comments.moderation.auto_approve', false)) {
            return true;
        }

        $userId = $data['user_id'] ?? auth()->id();

        if (!$userId) {
            return false;
        }

        if (config('comments.moderation.auto_approve_verified_users', true)) {
            $user = config('auth.providers.users.model')::find($userId);
            return $user?->hasVerifiedEmail() ?? false;
        }

        return false;
    }
}
