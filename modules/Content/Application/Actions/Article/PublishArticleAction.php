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
 * Handles article publication workflow including immediate publishing,
 * scheduled publishing, and unpublishing. Uses state machine pattern
 * for managing article lifecycle transitions.
 *
 * @package Modules\Content\Application\Actions\Article
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PublishArticleAction extends Action
{
    public function __construct(
        private readonly ArticleRepository $repository
    ) {}

    /**
     * Publish article immediately.
     *
     * Transitions article to published state and makes it publicly visible.
     * Sets published_at timestamp if not already set.
     *
     * @param Article $article The article instance to publish
     * 
     * @return Article The published article with updated status
     * 
     * @throws \Exception When state transition fails
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
     * Transitions article to scheduled state with future publication date.
     * Article will be automatically published at the specified time.
     *
     * @param Article $article The article instance to schedule
     * @param Carbon $publishAt The future publication date/time
     * 
     * @return Article The scheduled article with updated timestamps
     * 
     * @throws \Exception When state transition fails
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
     * Reverts article to draft status, removing it from public view.
     * Useful for making corrections or temporarily hiding content.
     *
     * @param Article $article The article instance to unpublish
     * 
     * @return Article The unpublished article with draft status
     * 
     * @throws \Exception When status update fails
     */
    public function unpublish(Article $article): Article
    {
        $previousStatus = $article->status;

        $article->update(['status' => 'draft']);

        event(new ArticleStatusChanged($article, $previousStatus, 'draft'));

        return $article->fresh();
    }
}
