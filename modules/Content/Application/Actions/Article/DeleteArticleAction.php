<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Article;

use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\Repositories\ArticleRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Delete Article Action.
 *
 * Handles soft and hard deletion of articles.
 */
final class DeleteArticleAction extends Action
{
    public function __construct(
        private readonly ArticleRepository $repository
    ) {}

    /**
     * Soft delete an article.
     *
     * @param Article $article The article to delete
     * @return bool
     */
    public function execute(Article $article): bool
    {
        $article->update(['deleted_by' => $this->userId()]);

        return $this->repository->delete($article->id);
    }

    /**
     * Permanently delete an article.
     *
     * @param string $id Article UUID
     * @return bool
     */
    public function forceDelete(string $id): bool
    {
        return $this->repository->forceDelete($id);
    }

    /**
     * Restore a soft-deleted article.
     *
     * @param string $id Article UUID
     * @return Article|null
     */
    public function restore(string $id): ?Article
    {
        return $this->repository->restore($id);
    }
}
