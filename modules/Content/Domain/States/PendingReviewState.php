<?php

declare(strict_types=1);

namespace Modules\Content\Domain\States;

/**
 * Pending Review State - Awaiting editorial review.
 */
final class PendingReviewState extends ArticleState
{
    public static function getValue(): string
    {
        return 'pending_review';
    }

    public static function getLabel(): string
    {
        return 'Pending Review';
    }

    public function allowedTransitions(): array
    {
        return [
            InReviewState::class,
            DraftState::class,
        ];
    }
}
