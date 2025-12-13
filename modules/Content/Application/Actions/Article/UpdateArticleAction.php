<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Article;

use Illuminate\Support\Str;
use Modules\Content\Domain\DTO\ArticleData;
use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\Repositories\ArticleRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Update Article Action.
 *
 * Handles updating an existing article with translations.
 */
final class UpdateArticleAction extends Action
{
    public function __construct(
        private readonly ArticleRepository $repository
    ) {}

    /**
     * Execute the action.
     *
     * @param Article $article The article to update
     * @param ArticleData $data Updated article data
     * @return Article The updated article
     */
    public function execute(Article $article, ArticleData $data): Article
    {
        return $this->transaction(function () use ($article, $data) {
            $this->repository->update($article->id, [
                'featured_image_id' => $data->featured_image_id ?? $article->featured_image_id,
                'type' => $data->type,
                'is_featured' => $data->is_featured,
                'allow_comments' => $data->allow_comments,
                'updated_by' => $this->userId(),
            ]);

            $this->updateTranslations($article, $data->translations);
            $this->updateReadingTime($article);

            return $article->fresh(['author', 'featuredImage', 'translations']);
        });
    }

    /**
     * Update or create translations.
     *
     * @param Article $article The article model
     * @param array<string, mixed> $translations Translations data
     */
    private function updateTranslations(Article $article, array $translations): void
    {
        foreach ($translations as $locale => $trans) {
            $article->translations()->updateOrCreate(
                ['locale' => $locale],
                [
                    'title' => $trans['title'],
                    'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                    'excerpt' => $trans['excerpt'] ?? null,
                    'content' => $trans['content'] ?? null,
                    'meta_title' => $trans['meta_title'] ?? null,
                    'meta_description' => $trans['meta_description'] ?? null,
                    'meta_keywords' => $trans['meta_keywords'] ?? null,
                ]
            );
        }
    }

    /**
     * Update the reading time based on content.
     *
     * @param Article $article The article model
     */
    private function updateReadingTime(Article $article): void
    {
        if (method_exists($article, 'calculateReadingTime')) {
            $article->reading_time = $article->calculateReadingTime();
            $article->save();
        }
    }
}
