<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Content\Domain\Models\Article;

/**
 * Article Status Changed Event.
 *
 * Dispatched when an article status changes.
 */
final class ArticleStatusChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Article $article,
        public readonly string $previousStatus,
        public readonly string $newStatus,
    ) {}
}
