<?php

declare(strict_types=1);

namespace Modules\Content\Contracts;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Content\Domain\Models\Article;

interface ArticleServiceContract
{
    // CRUD
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function find(string $id): ?Article;
    public function findBySlug(string $slug, ?string $locale = null): ?Article;
    public function create(array $data): Article;
    public function update(Article $article, array $data): Article;
    public function delete(Article $article): bool;
    public function forceDelete(Article $article): bool;
    public function restore(string $id): ?Article;

    // Workflow
    public function saveDraft(Article $article, array $data): Article;
    public function autoSave(Article $article, array $data): Article;
    public function submitForReview(Article $article): Article;
    public function startReview(Article $article): Article;
    public function approve(Article $article, ?string $notes = null): Article;
    public function reject(Article $article, ?string $notes = null): Article;
    public function publish(Article $article): Article;
    public function republish(Article $article): Article;
    public function schedule(Article $article, Carbon $publishAt): Article;
    public function cancelSchedule(Article $article): Article;
    public function unpublish(Article $article): Article;
    public function archive(Article $article): Article;
    public function unarchive(Article $article): Article;

    // Features
    public function duplicate(Article $article): Article;
    public function pin(Article $article): Article;
    public function unpin(Article $article): Article;
    public function convertType(Article $article, string $newType): Article;

    // Categories & Tags
    public function syncCategories(Article $article, array $categoryIds): Article;
    public function addTags(Article $article, array $tags): Article;
    public function removeTags(Article $article, array $tagIds): Article;

    // Related & Media
    public function attachRelated(Article $article, array $articleIds): Article;
    public function setFeaturedImage(Article $article, string $mediaId): Article;

    // Comments
    public function enableComments(Article $article): Article;
    public function disableComments(Article $article): Article;
    public function closeComments(Article $article): Article;

    // Revisions
    public function getRevisions(Article $article): \Illuminate\Database\Eloquent\Collection;
    public function restoreRevision(Article $article, int $revisionNumber): Article;

    // Social & Analytics
    public function shareOnSocial(Article $article, array $platforms): array;
    public function sendToNewsletter(Article $article): bool;
    public function getAnalytics(Article $article): array;
}
