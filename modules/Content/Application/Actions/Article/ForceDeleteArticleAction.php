<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Article;

use Modules\Content\Domain\Models\Article;
use Modules\Core\Application\Actions\Action;

/**
 * Force Delete Article Action.
 *
 * Permanently removes an article from the database.
 *
 * @package Modules\Content\Application\Actions\Article
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ForceDeleteArticleAction extends Action
{
    /**
     * Execute the force delete action.
     *
     * @param string $id The ID of the article to permanently delete
     *
     * @return bool True if deletion was successful
     */
    public function execute(string $id): bool
    {
        $article = Article::withTrashed()->find($id);
        return $article?->forceDelete() ?? false;
    }
}
