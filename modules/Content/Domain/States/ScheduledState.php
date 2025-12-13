<?php

declare(strict_types=1);

namespace Modules\Content\Domain\States;

/**
 * Scheduled State - Will be published at a future date.
 */
final class ScheduledState extends ArticleState
{
    public static function getValue(): string
    {
        return 'scheduled';
    }

    public static function getLabel(): string
    {
        return 'Scheduled';
    }

    public function allowedTransitions(): array
    {
        return [
            PublishedState::class,
            DraftState::class,
        ];
    }
}
