<?php

declare(strict_types=1);

namespace Modules\Content\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Content\Domain\Models\Article;

interface ArticleServiceContract
{
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function find(string $id): ?Article;

    public function findBySlug(string $slug): ?Article;

    public function create(array $data): Article;

    public function update(Article $article, array $data): Article;

    public function delete(Article $article): bool;

    public function forceDelete(string $id): bool;

    public function restore(string $id): ?Article;

    public function publish(Article $article): Article;

    public function unpublish(Article $article): Article;

    public function schedule(Article $article, \DateTimeInterface $publishAt): Article;

    public function duplicate(Article $article): Article;

    public function submitForReview(Article $article): Article;

    public function startReview(Article $article): Article;

    public function approve(Article $article, ?string $notes = null): Article;

    public function reject(Article $article, ?string $notes = null): Article;

    public function archive(Article $article): Article;

    public function unarchive(Article $article): Article;

    public function pin(Article $article): Article;

    public function unpin(Article $article): Article;

    public function syncCategories(Article $article, array $categoryIds): Article;

    public function addTags(Article $article, array $tags): Article;

    public function removeTags(Article $article, array $tagIds): Article;

    public function attachRelated(Article $article, array $articleIds): Article;

    public function enableComments(Article $article): Article;

    public function disableComments(Article $article): Article;

    public function closeComments(Article $article): Article;

    public function getRevisions(Article $article): array;

    public function restoreRevision(Article $article, int $revisionNumber): Article;

    public function shareOnSocial(Article $article, array $platforms): array;

    public function sendToNewsletter(Article $article): void;

    public function getAnalytics(Article $article): array;
}
