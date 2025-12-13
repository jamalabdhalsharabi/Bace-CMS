<?php

declare(strict_types=1);

namespace Modules\Content\Domain\States;

/**
 * Approved State - Ready for publication.
 */
final class ApprovedState extends ArticleState
{
    public static function getValue(): string
    {
        return 'approved';
    }

    public static function getLabel(): string
    {
        return 'Approved';
    }

    public function allowedTransitions(): array
    {
        return [
            PublishedState::class,
            ScheduledState::class,
            DraftState::class,
        ];
    }
}
