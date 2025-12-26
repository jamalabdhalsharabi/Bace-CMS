<?php

declare(strict_types=1);

namespace Modules\Content\Application\Services;

use Illuminate\Support\Str;
use Modules\Content\Domain\Models\Article;
use Modules\Taxonomy\Domain\Models\Taxonomy;

/**
 * Article Taxonomy Service.
 *
 * Manages article relationships with categories and tags.
 * Handles taxonomy synchronization and related articles.
 *
 * @package Modules\Content\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ArticleTaxonomyService
{
    /**
     * Sync article categories.
     *
     * @param Article $article The article
     * @param array<string> $categoryIds Category UUIDs
     * @return Article
     */
    public function syncCategories(Article $article, array $categoryIds): Article
    {
        if (method_exists($article, 'categories')) {
            $article->categories()->sync($categoryIds);
        }

        return $article->fresh(['categories']);
    }

    /**
     * Add tags to article.
     *
     * @param Article $article The article
     * @param array<string> $tags Tag names
     * @return Article
     */
    public function addTags(Article $article, array $tags): Article
    {
        if (!method_exists($article, 'tags')) {
            return $article;
        }

        $tagTaxonomyId = $this->getTagTaxonomyId();

        foreach ($tags as $tag) {
            $tagModel = Taxonomy::firstOrCreate(
                ['slug' => Str::slug($tag), 'type_id' => $tagTaxonomyId],
                []
            );

            if ($tagModel) {
                $article->tags()->syncWithoutDetaching($tagModel->id);
            }
        }

        return $article->fresh(['tags']);
    }

    /**
     * Remove tags from article.
     *
     * @param Article $article The article
     * @param array<string> $tagIds Tag UUIDs
     * @return Article
     */
    public function removeTags(Article $article, array $tagIds): Article
    {
        if (method_exists($article, 'tags')) {
            $article->tags()->detach($tagIds);
        }

        return $article->fresh(['tags']);
    }

    /**
     * Attach related articles.
     *
     * @param Article $article The main article
     * @param array<string> $articleIds Related article UUIDs
     * @return Article
     */
    public function attachRelated(Article $article, array $articleIds): Article
    {
        if (method_exists($article, 'relatedArticles')) {
            $article->relatedArticles()->sync($articleIds);
        }

        return $article->fresh(['relatedArticles']);
    }

    /**
     * Get the tag taxonomy type ID.
     *
     * @return string|null
     */
    private function getTagTaxonomyId(): ?string
    {
        return Taxonomy::where('slug', 'tags')->value('id');
    }
}
