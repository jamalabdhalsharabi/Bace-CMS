<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Article;

use Modules\Content\Domain\Models\Article;
use Modules\Core\Application\Actions\Action;

/**
 * Duplicate Article Action.
 *
 * Creates a copy of an existing article.
 */
final class DuplicateArticleAction extends Action
{
    /**
     * Execute the action.
     *
     * @param Article $article The article to duplicate
     * @return Article The duplicated article
     */
    public function execute(Article $article): Article
    {
        return $this->transaction(function () use ($article) {
            $clone = $article->replicate(['view_count', 'published_at', 'scheduled_at']);
            $clone->status = 'draft';
            $clone->created_by = $this->userId();
            $clone->save();

            foreach ($article->translations as $translation) {
                $clone->translations()->create([
                    'locale' => $translation->locale,
                    'title' => $translation->title . ' (Copy)',
                    'slug' => $translation->slug . '-copy-' . time(),
                    'excerpt' => $translation->excerpt,
                    'content' => $translation->content,
                    'meta_title' => $translation->meta_title,
                    'meta_description' => $translation->meta_description,
                    'meta_keywords' => $translation->meta_keywords,
                ]);
            }

            return $clone->fresh(['translations']);
        });
    }
}
