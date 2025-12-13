<?php

declare(strict_types=1);

namespace Modules\Content\Domain\States;

/**
 * Rejected State - Needs revision.
 */
final class RejectedState extends ArticleState
{
    public static function getValue(): string
    {
        return 'rejected';
    }

    public static function getLabel(): string
    {
        return 'Rejected';
    }

    public function allowedTransitions(): array
    {
        return [
            DraftState::class,
            PendingReviewState::class,
        ];
    }
}
