<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Article;

use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\Repositories\ArticleRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Delete Article Action.
 *
 * Handles article deletion operations including soft delete, permanent delete,
 * and restoration. Tracks deletion metadata for audit purposes.
 *
 * @package Modules\Content\Application\Actions\Article
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class DeleteArticleAction extends Action
{
    public function __construct(
        private readonly ArticleRepository $repository
    ) {}

    /**
     * Soft delete an article.
     *
     * Marks article as deleted while preserving data for potential restoration.
     * Records the deleting user for audit trail.
     *
     * @param Article $article The article instance to soft delete
     * 
     * @return bool True if deletion was successful
     * 
     * @throws \Exception When deletion fails
     */
    public function execute(Article $article): bool
    {
        $article->update(['deleted_by' => $this->userId()]);

        return $this->repository->delete($article->id);
    }

    /**
     * Permanently delete an article.
     *
     * Permanently removes article and all translations from database.
     * This operation cannot be undone.
     *
     * @param string $id The article UUID to permanently delete
     * 
     * @return bool True if permanent deletion was successful
     * 
     * @throws \Exception When permanent deletion fails
     */
    public function forceDelete(string $id): bool
    {
        return $this->repository->forceDelete($id);
    }

    /**
     * Restore a soft-deleted article.
     *
     * Restores a previously soft-deleted article back to active status.
     *
     * @param string $id The article UUID to restore
     * 
     * @return Article|null The restored article or null if not found
     * 
     * @throws \Exception When restoration fails
     */
    public function restore(string $id): ?Article
    {
        return $this->repository->restore($id);
    }
}
