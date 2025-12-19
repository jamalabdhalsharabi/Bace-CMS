<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Article;

use Modules\Content\Domain\Models\Article;
use Modules\Core\Application\Actions\Action;

/**
 * Restore Article Action.
 *
 * Restores a soft-deleted article.
 *
 * @package Modules\Content\Application\Actions\Article
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class RestoreArticleAction extends Action
{
    /**
     * Execute the restore action.
     *
     * @param string $id The ID of the article to restore
     *
     * @return Article|null The restored article or null if not found
     */
    public function execute(string $id): ?Article
    {
        $article = Article::withTrashed()->find($id);
        $article?->restore();
        return $article;
    }
}
