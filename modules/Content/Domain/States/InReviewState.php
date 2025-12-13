<?php

declare(strict_types=1);

namespace Modules\Content\Domain\States;

/**
 * In Review State - Currently being reviewed.
 */
final class InReviewState extends ArticleState
{
    public static function getValue(): string
    {
        return 'in_review';
    }

    public static function getLabel(): string
    {
        return 'In Review';
    }

    public function allowedTransitions(): array
    {
        return [
            ApprovedState::class,
            RejectedState::class,
            DraftState::class,
        ];
    }
}
