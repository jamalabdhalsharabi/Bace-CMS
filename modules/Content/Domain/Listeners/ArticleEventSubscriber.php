<?php

declare(strict_types=1);

namespace Modules\Content\Domain\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Facades\Log;
use Modules\Content\Domain\Events\ArticleCreated;
use Modules\Content\Domain\Events\ArticlePublished;
use Modules\Content\Domain\Events\ArticleStatusChanged;

/**
 * Article Event Subscriber.
 *
 * Handles article-related events for side effects like
 * notifications, indexing, caching, etc.
 */
final class ArticleEventSubscriber
{
    /**
     * Handle article created event.
     */
    public function handleArticleCreated(ArticleCreated $event): void
    {
        Log::info('Article created', [
            'id' => $event->article->id,
            'title' => $event->article->title,
        ]);

        // TODO: Notify editors, add to moderation queue, etc.
    }

    /**
     * Handle article published event.
     */
    public function handleArticlePublished(ArticlePublished $event): void
    {
        Log::info('Article published', [
            'id' => $event->article->id,
            'title' => $event->article->title,
        ]);

        // TODO: Clear caches, update search index, notify subscribers
        // cache()->tags('articles')->flush();
        // dispatch(new IndexArticleJob($event->article));
        // dispatch(new NotifySubscribersJob($event->article));
    }

    /**
     * Handle article status changed event.
     */
    public function handleArticleStatusChanged(ArticleStatusChanged $event): void
    {
        Log::info('Article status changed', [
            'id' => $event->article->id,
            'from' => $event->previousStatus,
            'to' => $event->newStatus,
        ]);

        // TODO: Send notifications based on status change
        // if ($event->newStatus === 'rejected') {
        //     Mail::to($event->article->author)->send(new ArticleRejected($event->article));
        // }
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): array
    {
        return [
            ArticleCreated::class => 'handleArticleCreated',
            ArticlePublished::class => 'handleArticlePublished',
            ArticleStatusChanged::class => 'handleArticleStatusChanged',
        ];
    }
}
