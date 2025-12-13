<?php

declare(strict_types=1);

namespace Modules\Content\Domain\States;

/**
 * Archived State - No longer active.
 */
final class ArchivedState extends ArticleState
{
    public static function getValue(): string
    {
        return 'archived';
    }

    public static function getLabel(): string
    {
        return 'Archived';
    }

    public function allowedTransitions(): array
    {
        return [
            DraftState::class,
        ];
    }
}
