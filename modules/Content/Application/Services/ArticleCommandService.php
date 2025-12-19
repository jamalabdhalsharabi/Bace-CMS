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
 * Application service responsible for orchestrating all write operations
 * (commands) related to articles. Follows the Command pattern and CQRS
 * principles by separating write operations from read operations.
 *
 * This service acts as a facade that delegates specific operations to
 * dedicated Action classes, ensuring Single Responsibility Principle
 * and making each operation independently testable.
 *
 * Responsibilities:
 * - Create new articles with translations
 * - Update existing articles
 * - Publish/unpublish/schedule articles
 * - Delete/restore articles (soft delete)
 * - Duplicate articles with translations
 *
 * @package Modules\Content\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 *
 * @see ArticleQueryService For read operations
 * @see CreateArticleAction
 * @see UpdateArticleAction
 * @see PublishArticleAction
 */
final class ArticleCommandService
{
    /**
     * Create a new ArticleCommandService instance.
     *
     * All action dependencies are injected via constructor,
     * enabling loose coupling and easy testing through mocking.
     *
     * @param CreateArticleAction    $createAction    Handles article creation
     * @param UpdateArticleAction    $updateAction    Handles article updates
     * @param PublishArticleAction   $publishAction   Handles publish/unpublish/schedule
     * @param DeleteArticleAction    $deleteAction    Handles delete/restore operations
     * @param DuplicateArticleAction $duplicateAction Handles article duplication
     */
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
