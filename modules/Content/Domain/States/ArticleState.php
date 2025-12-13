<?php

declare(strict_types=1);

namespace Modules\Content\Domain\States;

use Modules\Content\Domain\Models\Article;
use Modules\Core\Domain\States\State;

/**
 * Base Article State.
 *
 * Defines the state machine for article workflow.
 */
abstract class ArticleState extends State
{
    /**
     * State mapping for articles.
     *
     * @var array<string, class-string<ArticleState>>
     */
    public const STATES = [
        'draft' => DraftState::class,
        'pending_review' => PendingReviewState::class,
        'in_review' => InReviewState::class,
        'approved' => ApprovedState::class,
        'rejected' => RejectedState::class,
        'published' => PublishedState::class,
        'scheduled' => ScheduledState::class,
        'archived' => ArchivedState::class,
    ];

    /**
     * Get state from article model.
     *
     * @param Article $article The article model
     * @return ArticleState
     */
    public static function fromArticle(Article $article): ArticleState
    {
        return self::fromModel($article, self::STATES);
    }
}
