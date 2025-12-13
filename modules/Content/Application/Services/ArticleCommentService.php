<?php

declare(strict_types=1);

namespace Modules\Content\Application\Services;

use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\Repositories\ArticleRepository;

/**
 * Article Comment Service.
 *
 * Manages article commenting settings.
 * Single Responsibility: Comment configuration.
 */
final class ArticleCommentService
{
    public function __construct(
        private readonly ArticleRepository $repository
    ) {}

    /**
     * Enable comments on an article.
     *
     * @param Article $article The article
     * @return Article
     */
    public function enableComments(Article $article): Article
    {
        $this->repository->update($article->id, [
            'allow_comments' => true,
            'comments_closed_at' => null,
        ]);

        return $article->fresh();
    }

    /**
     * Disable comments on an article.
     *
     * @param Article $article The article
     * @return Article
     */
    public function disableComments(Article $article): Article
    {
        $this->repository->update($article->id, [
            'allow_comments' => false,
        ]);

        return $article->fresh();
    }

    /**
     * Close comments on an article (no new comments).
     *
     * @param Article $article The article
     * @return Article
     */
    public function closeComments(Article $article): Article
    {
        $this->repository->update($article->id, [
            'comments_closed_at' => now(),
        ]);

        return $article->fresh();
    }
}
