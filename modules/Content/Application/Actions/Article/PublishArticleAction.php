<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Article;

use Carbon\Carbon;
use Modules\Content\Domain\Events\ArticlePublished;
use Modules\Content\Domain\Events\ArticleStatusChanged;
use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\Repositories\ArticleRepository;
use Modules\Content\Domain\States\ArticleState;
use Modules\Content\Domain\States\PublishedState;
use Modules\Content\Domain\States\ScheduledState;
use Modules\Core\Application\Actions\Action;

/**
 * Publish Article Action.
 *
 * Handles publishing an article immediately or scheduling for later.
 */
final class PublishArticleAction extends Action
{
    public function __construct(
        private readonly ArticleRepository $repository
    ) {}

    /**
     * Publish article immediately.
     *
     * @param Article $article The article to publish
     * @return Article The published article
     */
    public function execute(Article $article): Article
    {
        $previousStatus = $article->status;
        $state = ArticleState::fromArticle($article);
        $state->transitionTo(PublishedState::class);

        $article->update(['published_at' => $article->published_at ?? now()]);

        event(new ArticlePublished($article));
        event(new ArticleStatusChanged($article, $previousStatus, 'published'));

        return $article->fresh();
    }

    /**
     * Schedule article for future publication.
     *
     * @param Article $article The article to schedule
     * @param Carbon $publishAt When to publish
     * @return Article The scheduled article
     */
    public function schedule(Article $article, Carbon $publishAt): Article
    {
        $previousStatus = $article->status;
        $state = ArticleState::fromArticle($article);
        $state->transitionTo(ScheduledState::class);

        $article->update(['published_at' => $publishAt, 'scheduled_at' => $publishAt]);

        event(new ArticleStatusChanged($article, $previousStatus, 'scheduled'));

        return $article->fresh();
    }

    /**
     * Unpublish a published article.
     *
     * @param Article $article The article to unpublish
     * @return Article The unpublished article
     */
    public function unpublish(Article $article): Article
    {
        $previousStatus = $article->status;

        $article->update(['status' => 'draft']);

        event(new ArticleStatusChanged($article, $previousStatus, 'draft'));

        return $article->fresh();
    }
}
