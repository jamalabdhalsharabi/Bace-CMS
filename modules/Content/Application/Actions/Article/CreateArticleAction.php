<?php

declare(strict_types=1);

namespace Modules\Content\Application\Actions\Article;

use Illuminate\Support\Str;
use Modules\Content\Domain\DTO\ArticleData;
use Modules\Content\Domain\Events\ArticleCreated;
use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\Repositories\ArticleRepository;
use Modules\Core\Application\Actions\Action;

/**
 * Create Article Action.
 *
 * Handles the creation of new articles with multi-language support.
 * Creates the article record along with all translations, calculates
 * reading time, and triggers creation events for system notifications.
 *
 * @package Modules\Content\Application\Actions\Article
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class CreateArticleAction extends Action
{
    /**
     * Create a new CreateArticleAction instance.
     *
     * @param ArticleRepository $repository The article repository for data operations
     */
    public function __construct(
        private readonly ArticleRepository $repository
    ) {}

    /**
     * Execute the article creation action.
     *
     * Creates a new article with the provided data including all translations.
     * The action performs the following steps:
     * 1. Creates the main article record
     * 2. Creates translation records for all provided locales
     * 3. Calculates and updates reading time based on content
     * 4. Triggers ArticleCreated event for notifications
     *
     * @param ArticleData $data The validated article data transfer object
     * 
     * @return Article The newly created article with relationships loaded
     * 
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException When related models not found
     * @throws \Exception When article creation fails
     */
    public function execute(ArticleData $data): Article
    {
        return $this->transaction(function () use ($data) {
            $article = $this->repository->create([
                'author_id' => $data->author_id ?? $this->userId(),
                'featured_image_id' => $data->featured_image_id,
                'type' => $data->type,
                'status' => $data->status,
                'is_featured' => $data->is_featured,
                'allow_comments' => $data->allow_comments,
                'view_count' => 0,
                'reading_time' => 0,
                'created_by' => $this->userId(),
            ]);

            $this->createTranslations($article, $data->translations);
            $this->updateReadingTime($article);

            event(new ArticleCreated($article));

            return $article->fresh(['author', 'featuredImage', 'translations']);
        });
    }

    /**
     * Create translations for the article.
     *
     * @param Article $article The article model
     * @param array<string, mixed> $translations Translations data
     */
    private function createTranslations(Article $article, array $translations): void
    {
        foreach ($translations as $locale => $trans) {
            $article->translations()->create([
                'locale' => $locale,
                'title' => $trans['title'],
                'slug' => $trans['slug'] ?? Str::slug($trans['title']),
                'excerpt' => $trans['excerpt'] ?? null,
                'content' => $trans['content'] ?? null,
                'meta_title' => $trans['meta_title'] ?? null,
                'meta_description' => $trans['meta_description'] ?? null,
                'meta_keywords' => $trans['meta_keywords'] ?? null,
            ]);
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
