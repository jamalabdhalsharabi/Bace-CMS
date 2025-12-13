<?php

declare(strict_types=1);

namespace Modules\Content\Domain\States;

/**
 * Draft State - Initial state for articles.
 */
final class DraftState extends ArticleState
{
    public static function getValue(): string
    {
        return 'draft';
    }

    public static function getLabel(): string
    {
        return 'Draft';
    }

    public function allowedTransitions(): array
    {
        return [
            PendingReviewState::class,
            PublishedState::class,
            ScheduledState::class,
            ArchivedState::class,
        ];
    }
}
