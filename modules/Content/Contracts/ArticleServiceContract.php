<?php

declare(strict_types=1);

namespace Modules\Content\Contracts;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Modules\Content\Domain\Models\Article;

/**
 * Interface ArticleServiceContract
 * 
 * Defines the contract for article/blog post management services.
 * Handles CRUD, workflow, features, categories, tags, related content,
 * comments, revisions, social sharing, and analytics.
 * 
 * @package Modules\Content\Contracts
 */
interface ArticleServiceContract
{
    /**
     * Get paginated list of articles with optional filters.
     *
     * @param array $filters Filter criteria (status, category, author, etc.)
     * @param int $perPage Items per page
     * @return LengthAwarePaginator
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Find an article by its ID.
     *
     * @param string $id The article UUID
     * @return Article|null
     */
    public function find(string $id): ?Article;

    /**
     * Find an article by its slug and optional locale.
     *
     * @param string $slug The article slug
     * @param string|null $locale Optional locale code
     * @return Article|null
     */
    public function findBySlug(string $slug, ?string $locale = null): ?Article;

    /**
     * Create a new article.
     *
     * @param array $data Article data including translations
     * @return Article
     */
    public function create(array $data): Article;

    /**
     * Update an existing article.
     *
     * @param Article $article The article to update
     * @param array $data Updated data
     * @return Article
     */
    public function update(Article $article, array $data): Article;

    /**
     * Soft delete an article.
     *
     * @param Article $article The article to delete
     * @return bool
     */
    public function delete(Article $article): bool;

    /**
     * Permanently delete an article.
     *
     * @param Article $article The article to force delete
     * @return bool
     */
    public function forceDelete(Article $article): bool;

    /**
     * Restore a soft-deleted article.
     *
     * @param string $id The article UUID
     * @return Article|null
     */
    public function restore(string $id): ?Article;

    /**
     * Save article as draft with updated data.
     *
     * @param Article $article The article
     * @param array $data Draft data
     * @return Article
     */
    public function saveDraft(Article $article, array $data): Article;

    /**
     * Auto-save article content periodically.
     *
     * @param Article $article The article
     * @param array $data Content to auto-save
     * @return Article
     */
    public function autoSave(Article $article, array $data): Article;

    /**
     * Submit article for editorial review.
     *
     * @param Article $article The article to submit
     * @return Article
     */
    public function submitForReview(Article $article): Article;

    /**
     * Start the review process for an article.
     *
     * @param Article $article The article to review
     * @return Article
     */
    public function startReview(Article $article): Article;

    /**
     * Approve an article after review.
     *
     * @param Article $article The article to approve
     * @param string|null $notes Optional approval notes
     * @return Article
     */
    public function approve(Article $article, ?string $notes = null): Article;

    /**
     * Reject an article after review.
     *
     * @param Article $article The article to reject
     * @param string|null $notes Optional rejection reason
     * @return Article
     */
    public function reject(Article $article, ?string $notes = null): Article;

    /**
     * Publish an article immediately.
     *
     * @param Article $article The article to publish
     * @return Article
     */
    public function publish(Article $article): Article;

    /**
     * Republish a previously published article.
     *
     * @param Article $article The article to republish
     * @return Article
     */
    public function republish(Article $article): Article;

    /**
     * Schedule an article for future publication.
     *
     * @param Article $article The article to schedule
     * @param Carbon $publishAt Publication date and time
     * @return Article
     */
    public function schedule(Article $article, Carbon $publishAt): Article;

    /**
     * Cancel scheduled publication.
     *
     * @param Article $article The scheduled article
     * @return Article
     */
    public function cancelSchedule(Article $article): Article;

    /**
     * Unpublish a published article.
     *
     * @param Article $article The article to unpublish
     * @return Article
     */
    public function unpublish(Article $article): Article;

    /**
     * Archive an article.
     *
     * @param Article $article The article to archive
     * @return Article
     */
    public function archive(Article $article): Article;

    /**
     * Restore an article from archive.
     *
     * @param Article $article The archived article
     * @return Article
     */
    public function unarchive(Article $article): Article;

    /**
     * Duplicate an article.
     *
     * @param Article $article The article to duplicate
     * @return Article The duplicated article
     */
    public function duplicate(Article $article): Article;

    /**
     * Pin an article to the top of listings.
     *
     * @param Article $article The article to pin
     * @return Article
     */
    public function pin(Article $article): Article;

    /**
     * Unpin a pinned article.
     *
     * @param Article $article The article to unpin
     * @return Article
     */
    public function unpin(Article $article): Article;

    /**
     * Convert article to a different type.
     *
     * @param Article $article The article to convert
     * @param string $newType The new article type
     * @return Article
     */
    public function convertType(Article $article, string $newType): Article;

    /**
     * Sync article categories.
     *
     * @param Article $article The article
     * @param array $categoryIds Array of category IDs
     * @return Article
     */
    public function syncCategories(Article $article, array $categoryIds): Article;

    /**
     * Add tags to an article.
     *
     * @param Article $article The article
     * @param array $tags Array of tag names or IDs
     * @return Article
     */
    public function addTags(Article $article, array $tags): Article;

    /**
     * Remove tags from an article.
     *
     * @param Article $article The article
     * @param array $tagIds Array of tag IDs to remove
     * @return Article
     */
    public function removeTags(Article $article, array $tagIds): Article;

    /**
     * Attach related articles.
     *
     * @param Article $article The article
     * @param array $articleIds Array of related article IDs
     * @return Article
     */
    public function attachRelated(Article $article, array $articleIds): Article;

    /**
     * Set the featured image for an article.
     *
     * @param Article $article The article
     * @param string $mediaId The media ID to use as featured image
     * @return Article
     */
    public function setFeaturedImage(Article $article, string $mediaId): Article;

    /**
     * Enable comments on an article.
     *
     * @param Article $article The article
     * @return Article
     */
    public function enableComments(Article $article): Article;

    /**
     * Disable comments on an article.
     *
     * @param Article $article The article
     * @return Article
     */
    public function disableComments(Article $article): Article;

    /**
     * Close comments on an article (no new comments).
     *
     * @param Article $article The article
     * @return Article
     */
    public function closeComments(Article $article): Article;

    /**
     * Get all revisions for an article.
     *
     * @param Article $article The article
     * @return \Illuminate\Database\Eloquent\Collection Collection of revisions
     */
    public function getRevisions(Article $article): \Illuminate\Database\Eloquent\Collection;

    /**
     * Restore an article to a specific revision.
     *
     * @param Article $article The article
     * @param int $revisionNumber The revision number to restore
     * @return Article The restored article
     */
    public function restoreRevision(Article $article, int $revisionNumber): Article;

    /**
     * Share article on social media platforms.
     *
     * @param Article $article The article to share
     * @param array $platforms Array of platform names (facebook, twitter, etc.)
     * @return array Results for each platform
     */
    public function shareOnSocial(Article $article, array $platforms): array;

    /**
     * Send article to newsletter subscribers.
     *
     * @param Article $article The article to send
     * @return bool Success status
     */
    public function sendToNewsletter(Article $article): bool;

    /**
     * Get analytics data for an article.
     *
     * @param Article $article The article
     * @return array Analytics data (views, shares, engagement, etc.)
     */
    public function getAnalytics(Article $article): array;
}
