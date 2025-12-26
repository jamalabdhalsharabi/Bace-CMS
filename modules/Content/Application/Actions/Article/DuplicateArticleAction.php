<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Article;

use Modules\Content\Domain\Models\Article;
use Modules\Core\Application\Actions\Action;

/**
 * Duplicate Article Action.
 *
 * Creates a complete copy of an existing article including all translations.
 * Useful for creating similar content or templates.
 *
 * @package Modules\Content\Application\Actions\Article
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class DuplicateArticleAction extends Action
{
    /**
     * Execute the article duplication action.
     *
     * Creates a new article as a copy of the source article with:
     * - All translations duplicated with '(Copy)' suffix
     * - Status reset to 'draft'
     * - View count reset to 0
     * - New unique slugs to avoid conflicts
     *
     * @param Article $article The source article to duplicate
     * 
     * @return Article The newly created duplicate article
     * 
     * @throws \Exception When duplication fails
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
