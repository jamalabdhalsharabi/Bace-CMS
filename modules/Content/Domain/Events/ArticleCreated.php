<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Content\Domain\Models\Article;

/**
 * Article Created Event.
 *
 * Dispatched when a new article is created.
 */
final class ArticleCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Article $article
    ) {}
}
