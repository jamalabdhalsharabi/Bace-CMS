<?php

declare(strict_types=1);

namespace Modules\Content\Application\Services;

use Modules\Content\Domain\Models\Article;
use Modules\Content\Domain\Repositories\ArticleRepository;

/**
 * Article Media Service.
 *
 * Manages article media relationships (featured image, gallery).
 * Single Responsibility: Media attachments.
 */
final class ArticleMediaService
{
    public function __construct(
        private readonly ArticleRepository $repository
    ) {}

    /**
     * Set the featured image for an article.
     *
     * @param Article $article The article
     * @param string $mediaId Media UUID
     * @return Article
     */
    public function setFeaturedImage(Article $article, string $mediaId): Article
    {
        $this->repository->update($article->id, [
            'featured_image_id' => $mediaId,
        ]);

        return $article->fresh(['featuredImage']);
    }

    /**
     * Remove the featured image from an article.
     *
     * @param Article $article The article
     * @return Article
     */
    public function removeFeaturedImage(Article $article): Article
    {
        $this->repository->update($article->id, [
            'featured_image_id' => null,
        ]);

        return $article->fresh();
    }
}
