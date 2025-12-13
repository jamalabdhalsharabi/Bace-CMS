<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Modules\Content\Domain\Models\Article;

/**
 * Article Published Event.
 *
 * Dispatched when an article is published.
 */
final class ArticlePublished
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public readonly Article $article
    ) {}
}
