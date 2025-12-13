<?php

declare(strict_types=1);

namespace Modules\Content\Application\Services;

use Carbon\Carbon;
use Modules\Content\Application\Actions\Article\CreateArticleAction;
use Modules\Content\Application\Actions\Article\DeleteArticleAction;
use Modules\Content\Application\Actions\Article\DuplicateArticleAction;
use Modules\Content\Application\Actions\Article\PublishArticleAction;
use Modules\Content\Application\Actions\Article\UpdateArticleAction;
use Modules\Content\Domain\DTO\ArticleData;
use Modules\Content\Domain\Models\Article;

/**
 * Article Command Service.
 *
 * Handles write operations for articles.
 * Orchestrates actions for complex workflows.
 */
final class ArticleCommandService
{
    public function __construct(
        private readonly CreateArticleAction $createAction,
        private readonly UpdateArticleAction $updateAction,
        private readonly PublishArticleAction $publishAction,
        private readonly DeleteArticleAction $deleteAction,
        private readonly DuplicateArticleAction $duplicateAction,
    ) {}

    /**
     * Create a new article.
     *
     * @param ArticleData $data Article data
     * @return Article
     */
    public function create(ArticleData $data): Article
    {
        return $this->createAction->execute($data);
    }

    /**
     * Update an existing article.
     *
     * @param Article $article The article to update
     * @param ArticleData $data Updated data
     * @return Article
     */
    public function update(Article $article, ArticleData $data): Article
    {
        return $this->updateAction->execute($article, $data);
    }

    /**
     * Publish an article.
     *
     * @param Article $article The article to publish
     * @return Article
     */
    public function publish(Article $article): Article
    {
        return $this->publishAction->execute($article);
    }

    /**
     * Unpublish an article.
     *
     * @param Article $article The article to unpublish
     * @return Article
     */
    public function unpublish(Article $article): Article
    {
        return $this->publishAction->unpublish($article);
    }

    /**
     * Schedule an article for future publication.
     *
     * @param Article $article The article to schedule
     * @param Carbon $publishAt When to publish
     * @return Article
     */
    public function schedule(Article $article, Carbon $publishAt): Article
    {
        return $this->publishAction->schedule($article, $publishAt);
    }

    /**
     * Delete an article.
     *
     * @param Article $article The article to delete
     * @return bool
     */
    public function delete(Article $article): bool
    {
        return $this->deleteAction->execute($article);
    }

    /**
     * Force delete an article.
     *
     * @param string $id Article UUID
     * @return bool
     */
    public function forceDelete(string $id): bool
    {
        return $this->deleteAction->forceDelete($id);
    }

    /**
     * Restore a deleted article.
     *
     * @param string $id Article UUID
     * @return Article|null
     */
    public function restore(string $id): ?Article
    {
        return $this->deleteAction->restore($id);
    }

    /**
     * Duplicate an article.
     *
     * @param Article $article The article to duplicate
     * @return Article
     */
    public function duplicate(Article $article): Article
    {
        return $this->duplicateAction->execute($article);
    }
}
