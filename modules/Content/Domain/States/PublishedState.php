<?php

declare(strict_types=1);

namespace Modules\Content\Domain\States;

/**
 * Published State - Live and visible.
 */
final class PublishedState extends ArticleState
{
    public static function getValue(): string
    {
        return 'published';
    }

    public static function getLabel(): string
    {
        return 'Published';
    }

    public function allowedTransitions(): array
    {
        return [
            DraftState::class,
            ArchivedState::class,
        ];
    }
}
