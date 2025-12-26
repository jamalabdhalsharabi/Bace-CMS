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
     * Execute the permanent article deletion action.
     *
     * Permanently removes article and all related data from database.
     * This bypasses soft delete and cannot be undone.
     *
     * @param string $id The article UUID to permanently delete
     * 
     * @return bool True if permanent deletion was successful, false otherwise
     * 
     * @throws \Exception When permanent deletion fails
     */
    public function execute(string $id): bool
    {
        $article = Article::withTrashed()->find($id);
        return $article?->forceDelete() ?? false;
    }
}
