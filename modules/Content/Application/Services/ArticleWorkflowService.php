<?php

declare(strict_types=1);

namespace Modules\Content\Application\Services;

use Modules\Content\Domain\Events\ArticleStatusChanged;
use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\Repositories\ArticleRepository;
use Modules\Content\Domain\States\ApprovedState;
use Modules\Content\Domain\States\ArchivedState;
use Modules\Content\Domain\States\ArticleState;
use Modules\Content\Domain\States\DraftState;
use Modules\Content\Domain\States\InReviewState;
use Modules\Content\Domain\States\PendingReviewState;
use Modules\Content\Domain\States\RejectedState;

/**
 * Article Workflow Service.
 *
 * Manages article review and approval workflow using State Machine pattern.
 * Handles state transitions for editorial workflow.
 *
 * @package Modules\Content\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ArticleWorkflowService
{
    /**
     * Create a new ArticleWorkflowService instance.
     *
     * @param ArticleRepository $repository The article repository
     */
    public function __construct(
        private readonly ArticleRepository $repository
    ) {}

    /**
     * Submit article for review.
     *
     * @param Article $article The article to submit
     * @return Article
     */
    public function submitForReview(Article $article): Article
    {
        return $this->transition($article, PendingReviewState::class);
    }

    /**
     * Start reviewing an article.
     *
     * @param Article $article The article to review
     * @return Article
     */
    public function startReview(Article $article): Article
    {
        return $this->transition($article, InReviewState::class);
    }

    /**
     * Approve an article.
     *
     * @param Article $article The article to approve
     * @param string|null $notes Approval notes
     * @return Article
     */
    public function approve(Article $article, ?string $notes = null): Article
    {
        $article = $this->transition($article, ApprovedState::class);

        if ($notes) {
            $meta = $article->meta ?? [];
            $meta['review_notes'] = $notes;
            $article->update(['meta' => $meta]);
        }

        return $article->fresh();
    }

    /**
     * Reject an article.
     *
     * @param Article $article The article to reject
     * @param string|null $notes Rejection reason
     * @return Article
     */
    public function reject(Article $article, ?string $notes = null): Article
    {
        $article = $this->transition($article, RejectedState::class);

        if ($notes) {
            $meta = $article->meta ?? [];
            $meta['review_notes'] = $notes;
            $article->update(['meta' => $meta]);
        }

        return $article->fresh();
    }

    /**
     * Archive an article.
     *
     * @param Article $article The article to archive
     * @return Article
     */
    public function archive(Article $article): Article
    {
        return $this->transition($article, ArchivedState::class);
    }

    /**
     * Unarchive an article (back to draft).
     *
     * @param Article $article The article to unarchive
     * @return Article
     */
    public function unarchive(Article $article): Article
    {
        return $this->transition($article, DraftState::class);
    }

    /**
     * Perform a state transition.
     *
     * @param Article $article The article model
     * @param class-string<ArticleState> $targetState Target state class
     * @return Article
     */
    private function transition(Article $article, string $targetState): Article
    {
        $previousStatus = $article->status;
        $state = ArticleState::fromArticle($article);
        $state->transitionTo($targetState);

        event(new ArticleStatusChanged($article, $previousStatus, $targetState::getValue()));

        return $article->fresh();
    }
}
