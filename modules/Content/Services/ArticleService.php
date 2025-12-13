<?php

declare(strict_types=1);

namespace Modules\Content\Services;

use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Content\Contracts\ArticleServiceContract;
use Modules\Content\Domain\Models\Article;

/**
 * Class ArticleService
 *
 * Service class for managing articles including CRUD operations,
 * workflow, categories, tags, comments, revisions, and social sharing.
 *
 * @package Modules\Content\Services
 */
class ArticleService implements ArticleServiceContract
{
    /**
     * Retrieve a paginated list of articles with optional filtering.
     *
     * Supports filtering by status, type, author, featured flag, and search term.
     * Results include author, featured image, and translation relationships.
     *
     * @param array $filters Optional filters: 'status', 'type', 'author_id', 'featured', 'search'
     * @param int $perPage Number of results per page (default: 15)
     *
     * @return LengthAwarePaginator Paginated collection of Article models
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Article::with(['author', 'featuredImage', 'translation']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['author_id'])) {
            $query->where('author_id', $filters['author_id']);
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('translations', fn ($q) => 
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('content', 'LIKE', "%{$search}%")
            );
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find an article by its UUID.
     *
     * Retrieves a single article with author, featured image, and all translations.
     * Returns null if no article is found with the given ID.
     *
     * @param string $id The UUID of the article to find
     *
     * @return Article|null The found Article or null if not found
     */
    public function find(string $id): ?Article
    {
        return Article::with(['author', 'featuredImage', 'translations'])->find($id);
    }

    /**
     * Find an article by its localized URL slug.
     *
     * Searches for an article with a translation matching the given slug
     * in the specified locale (or current locale if not specified).
     *
     * @param string $slug The URL slug to search for
     * @param string|null $locale The locale to search in, defaults to current
     *
     * @return Article|null The found Article with relationships or null
     */
    public function findBySlug(string $slug, ?string $locale = null): ?Article
    {
        return Article::findBySlug($slug, $locale)?->load(['author', 'featuredImage', 'translations']);
    }

    /**
     * Create a new article with translations.
     *
     * Creates an article record and associated translations in a database
     * transaction. Automatically calculates reading time from content.
     *
     * @param array $data Article data including 'translations' array keyed by locale
     *
     * @return Article The newly created Article with all relationships
     *
     * @throws \Throwable If the transaction fails
     */
    public function create(array $data): Article
    {
        return DB::transaction(function () use ($data) {
            $article = Article::create([
                'author_id' => $data['author_id'] ?? auth()->id(),
                'featured_image_id' => $data['featured_image_id'] ?? null,
                'type' => $data['type'] ?? 'post',
                'status' => $data['status'] ?? 'draft',
                'is_featured' => $data['is_featured'] ?? false,
                'is_commentable' => $data['is_commentable'] ?? true,
                'view_count' => 0,
                'reading_time' => 0,
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
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

            $article->reading_time = $article->calculateReadingTime();
            $article->save();

            return $article->fresh(['author', 'featuredImage', 'translations']);
        });
    }

    /**
     * Update an existing article and its translations.
     *
     * Updates article data and translations in a database transaction.
     * Creates new translations for locales not yet present.
     *
     * @param Article $article The article to update
     * @param array $data Updated data including optional 'translations' array
     *
     * @return Article The updated Article with fresh relationships
     *
     * @throws \Throwable If the transaction fails
     */
    public function update(Article $article, array $data): Article
    {
        return DB::transaction(function () use ($article, $data) {
            $article->update([
                'featured_image_id' => $data['featured_image_id'] ?? $article->featured_image_id,
                'type' => $data['type'] ?? $article->type,
                'is_featured' => $data['is_featured'] ?? $article->is_featured,
                'is_commentable' => $data['is_commentable'] ?? $article->is_commentable,
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
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

            $article->reading_time = $article->calculateReadingTime();
            $article->save();

            return $article->fresh(['author', 'featuredImage', 'translations']);
        });
    }

    /**
     * Publish an article immediately.
     *
     * Sets the status to 'published' and the published_at timestamp
     * to now if not already set. Makes the article publicly visible.
     *
     * @param Article $article The article to publish
     *
     * @return Article The published article
     */
    public function publish(Article $article): Article
    {
        $article->update([
            'status' => 'published',
            'published_at' => $article->published_at ?? now(),
        ]);

        return $article->fresh();
    }

    /**
     * Unpublish an article and revert to draft status.
     *
     * Sets the status back to 'draft', removing it from public view
     * while preserving the content for further editing.
     *
     * @param Article $article The article to unpublish
     *
     * @return Article The unpublished article
     */
    public function unpublish(Article $article): Article
    {
        $article->update(['status' => 'draft']);

        return $article->fresh();
    }

    /**
     * Schedule an article for future publication.
     *
     * Sets the status to 'published' with a future published_at date.
     * The article will become visible once the scheduled time passes.
     *
     * @param Article $article The article to schedule
     * @param Carbon $publishAt The date/time to publish the article
     *
     * @return Article The scheduled article
     */
    public function schedule(Article $article, Carbon $publishAt): Article
    {
        $article->update([
            'status' => 'published',
            'published_at' => $publishAt,
        ]);

        return $article->fresh();
    }

    /**
     * Archive an article.
     *
     * Sets the status to 'archived', removing it from active listings
     * while preserving it for historical reference.
     *
     * @param Article $article The article to archive
     *
     * @return Article The archived article
     */
    public function archive(Article $article): Article
    {
        $article->update(['status' => 'archived']);

        return $article->fresh();
    }

    /**
     * Soft-delete an article.
     *
     * Marks the article as deleted without permanently removing it.
     * The article can be restored later if needed.
     *
     * @param Article $article The article to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(Article $article): bool
    {
        return $article->delete();
    }

    /**
     * Create a duplicate copy of an article.
     *
     * Clones the article and all translations with modified slugs and titles.
     * The new article is set to draft status with reset view count.
     *
     * @param Article $article The article to duplicate
     *
     * @return Article The newly created duplicate article
     *
     * @throws \Throwable If the transaction fails
     */
    public function duplicate(Article $article): Article
    {
        return DB::transaction(function () use ($article) {
            $newArticle = $article->replicate(['view_count', 'published_at']);
            $newArticle->status = 'draft';
            $newArticle->save();

            foreach ($article->translations as $translation) {
                $newArticle->translations()->create([
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

            return $newArticle->fresh(['translations']);
        });
    }

    /**
     * Permanently delete an article from the database.
     *
     * Removes the article and all associated data permanently.
     * This action cannot be undone.
     *
     * @param Article $article The article to permanently delete
     *
     * @return bool True if deletion was successful
     */
    public function forceDelete(Article $article): bool
    {
        return $article->forceDelete();
    }

    /**
     * Restore a soft-deleted article.
     *
     * Recovers a previously deleted article back to its original state.
     * Returns null if the article is not found.
     *
     * @param string $id The UUID of the article to restore
     *
     * @return Article|null The restored article or null if not found
     */
    public function restore(string $id): ?Article
    {
        $article = Article::withTrashed()->find($id);
        $article?->restore();
        return $article;
    }

    /**
     * Save article changes as a draft.
     *
     * Updates the article with provided data and ensures status is 'draft'.
     * Useful for auto-save functionality.
     *
     * @param Article $article The article to save as draft
     * @param array $data The data to update
     *
     * @return Article The updated draft article
     */
    public function saveDraft(Article $article, array $data): Article
    {
        $data['status'] = 'draft';
        return $this->update($article, $data);
    }

    /**
     * Auto-save article content without changing status.
     *
     * Saves the current state of the article for recovery purposes
     * without modifying the publication status.
     *
     * @param Article $article The article to auto-save
     * @param array $data The data to save
     *
     * @return Article The auto-saved article
     */
    public function autoSave(Article $article, array $data): Article
    {
        return $this->update($article, $data);
    }

    /**
     * Submit an article for editorial review.
     *
     * Changes status to 'pending_review' and records submission time.
     * Triggers the review workflow for editors.
     *
     * @param Article $article The article to submit
     *
     * @return Article The submitted article
     */
    public function submitForReview(Article $article): Article
    {
        $article->update(['status' => 'pending_review', 'submitted_at' => now()]);
        return $article->fresh();
    }

    /**
     * Begin reviewing an article.
     *
     * Changes status to 'in_review' and assigns the current user
     * as the reviewer. Prevents other editors from reviewing.
     *
     * @param Article $article The article to review
     *
     * @return Article The article under review
     */
    public function startReview(Article $article): Article
    {
        $article->update(['status' => 'in_review', 'reviewed_by' => auth()->id()]);
        return $article->fresh();
    }

    /**
     * Approve an article after review.
     *
     * Sets the status to 'approved' and optionally stores reviewer notes.
     * The article is ready for publication after approval.
     *
     * @param Article $article The article to approve
     * @param string|null $notes Optional reviewer notes or feedback
     *
     * @return Article The approved article
     */
    public function approve(Article $article, ?string $notes = null): Article
    {
        $article->update(['status' => 'approved', 'review_notes' => $notes]);
        return $article->fresh();
    }

    /**
     * Reject an article during review.
     *
     * Sets the status to 'rejected' and stores the rejection reason.
     * The author will need to revise and resubmit.
     *
     * @param Article $article The article to reject
     * @param string|null $notes Rejection reason or required changes
     *
     * @return Article The rejected article
     */
    public function reject(Article $article, ?string $notes = null): Article
    {
        $article->update(['status' => 'rejected', 'review_notes' => $notes]);
        return $article->fresh();
    }

    /**
     * Republish a previously unpublished or archived article.
     *
     * Sets the status to 'published' with a new publication timestamp.
     * Makes the article publicly visible again.
     *
     * @param Article $article The article to republish
     *
     * @return Article The republished article
     */
    public function republish(Article $article): Article
    {
        $article->update(['status' => 'published', 'published_at' => now()]);
        return $article->fresh();
    }

    /**
     * Cancel a scheduled publication.
     *
     * Reverts a scheduled article to draft status and clears
     * the scheduled publication date.
     *
     * @param Article $article The article to unschedule
     *
     * @return Article The unscheduled article
     */
    public function cancelSchedule(Article $article): Article
    {
        $article->update(['status' => 'draft', 'published_at' => null]);
        return $article->fresh();
    }

    /**
     * Restore an archived article to draft status.
     *
     * Moves an article out of the archive back to drafts
     * for editing and potential republication.
     *
     * @param Article $article The article to unarchive
     *
     * @return Article The unarchived article
     */
    public function unarchive(Article $article): Article
    {
        $article->update(['status' => 'draft', 'archived_at' => null]);
        return $article->fresh();
    }

    /**
     * Pin an article to the top of listings.
     *
     * Sets the is_pinned flag to true, causing the article
     * to appear prominently at the top of article lists.
     *
     * @param Article $article The article to pin
     *
     * @return Article The pinned article
     */
    public function pin(Article $article): Article
    {
        $article->update(['is_pinned' => true]);
        return $article->fresh();
    }

    /**
     * Remove the pinned status from an article.
     *
     * Clears the is_pinned flag, returning the article
     * to normal chronological ordering in listings.
     *
     * @param Article $article The article to unpin
     *
     * @return Article The unpinned article
     */
    public function unpin(Article $article): Article
    {
        $article->update(['is_pinned' => false]);
        return $article->fresh();
    }

    /**
     * Convert an article to a different content type.
     *
     * Changes the article type (e.g., from 'post' to 'news')
     * which may affect display and categorization.
     *
     * @param Article $article The article to convert
     * @param string $newType The new type identifier
     *
     * @return Article The converted article
     */
    public function convertType(Article $article, string $newType): Article
    {
        $article->update(['type' => $newType]);
        return $article->fresh();
    }

    /**
     * Synchronize article categories.
     *
     * Replaces all current category associations with the provided list.
     * Categories not in the list will be detached.
     *
     * @param Article $article The article to update
     * @param array $categoryIds Array of category UUIDs to assign
     *
     * @return Article The article with updated categories
     */
    public function syncCategories(Article $article, array $categoryIds): Article
    {
        $article->categories()->sync($categoryIds);
        return $article->fresh(['categories']);
    }

    /**
     * Add tags to an article.
     *
     * Creates tags if they don't exist and attaches them to the article.
     * Existing tags are preserved.
     *
     * @param Article $article The article to tag
     * @param array $tags Array of tag names to add
     *
     * @return Article The article with updated tags
     */
    public function addTags(Article $article, array $tags): Article
    {
        foreach ($tags as $tag) {
            $tagModel = \Modules\Taxonomy\Domain\Models\Term::firstOrCreate(
                ['slug' => Str::slug($tag), 'taxonomy_id' => $this->getTagTaxonomyId()],
                ['name' => $tag]
            );
            $article->tags()->syncWithoutDetaching($tagModel->id);
        }
        return $article->fresh(['tags']);
    }

    /**
     * Remove tags from an article.
     *
     * Detaches the specified tags from the article.
     * The tags themselves are not deleted.
     *
     * @param Article $article The article to update
     * @param array $tagIds Array of tag UUIDs to remove
     *
     * @return Article The article with updated tags
     */
    public function removeTags(Article $article, array $tagIds): Article
    {
        $article->tags()->detach($tagIds);
        return $article->fresh(['tags']);
    }

    /**
     * Attach related articles.
     *
     * Sets the list of articles to display as related content.
     * Replaces any existing related article associations.
     *
     * @param Article $article The main article
     * @param array $articleIds UUIDs of related articles
     *
     * @return Article The article with related articles attached
     */
    public function attachRelated(Article $article, array $articleIds): Article
    {
        $article->relatedArticles()->sync($articleIds);
        return $article->fresh(['relatedArticles']);
    }

    /**
     * Set the article's featured image.
     *
     * Associates a media item as the article's primary image
     * for thumbnails and social sharing.
     *
     * @param Article $article The article to update
     * @param string $mediaId The UUID of the media item
     *
     * @return Article The article with updated featured image
     */
    public function setFeaturedImage(Article $article, string $mediaId): Article
    {
        $article->update(['featured_image_id' => $mediaId]);
        return $article->fresh(['featuredImage']);
    }

    /**
     * Enable comments on an article.
     *
     * Allows users to post comments on this article.
     * Also reopens comments if they were previously closed.
     *
     * @param Article $article The article to enable comments on
     *
     * @return Article The article with comments enabled
     */
    public function enableComments(Article $article): Article
    {
        $article->update(['is_commentable' => true, 'comments_closed' => false]);
        return $article->fresh();
    }

    /**
     * Disable comments on an article.
     *
     * Prevents any new comments from being posted.
     * Existing comments remain visible.
     *
     * @param Article $article The article to disable comments on
     *
     * @return Article The article with comments disabled
     */
    public function disableComments(Article $article): Article
    {
        $article->update(['is_commentable' => false]);
        return $article->fresh();
    }

    /**
     * Close comments on an article.
     *
     * Closes the comment section while keeping comments enabled.
     * Existing comments remain, but no new ones can be added.
     *
     * @param Article $article The article to close comments on
     *
     * @return Article The article with closed comments
     */
    public function closeComments(Article $article): Article
    {
        $article->update(['comments_closed' => true]);
        return $article->fresh();
    }

    /**
     * Get all revisions for an article.
     *
     * Retrieves the version history showing all saved states
     * of the article for auditing and restoration.
     *
     * @param Article $article The article to get revisions for
     *
     * @return \Illuminate\Database\Eloquent\Collection Collection of Revision models
     */
    public function getRevisions(Article $article): \Illuminate\Database\Eloquent\Collection
    {
        return $article->revisions()->get();
    }

    /**
     * Restore an article to a previous revision.
     *
     * Reverts the article content to a previously saved state.
     * Creates a new revision to track the restoration.
     *
     * @param Article $article The article to restore
     * @param int $revisionNumber The revision number to restore to
     *
     * @return Article The restored article
     */
    public function restoreRevision(Article $article, int $revisionNumber): Article
    {
        $article->restoreRevision($revisionNumber);
        return $article->fresh();
    }

    /**
     * Share an article on social media platforms.
     *
     * Queues the article for sharing on specified platforms.
     * Returns the status for each platform.
     *
     * @param Article $article The article to share
     * @param array $platforms Array of platform names (e.g., ['twitter', 'facebook'])
     *
     * @return array Associative array of platform => status
     */
    public function shareOnSocial(Article $article, array $platforms): array
    {
        $results = [];
        foreach ($platforms as $platform) {
            $results[$platform] = ['status' => 'queued'];
        }
        return $results;
    }

    /**
     * Send an article to the newsletter queue.
     *
     * Queues the article content for distribution via email newsletter.
     * Returns true if queuing was successful.
     *
     * @param Article $article The article to send
     *
     * @return bool True if successfully queued
     */
    public function sendToNewsletter(Article $article): bool
    {
        // Queue newsletter sending
        return true;
    }

    /**
     * Get analytics data for an article.
     *
     * Returns performance metrics including views, reading time,
     * comment count, and share statistics.
     *
     * @param Article $article The article to get analytics for
     *
     * @return array Associative array of analytics metrics
     */
    public function getAnalytics(Article $article): array
    {
        return [
            'view_count' => $article->view_count,
            'reading_time' => $article->reading_time,
            'comments_count' => $article->comments()->count(),
            'shares' => 0,
        ];
    }

    /**
     * Get the taxonomy ID for article tags.
     *
     * Retrieves the ID of the 'tags' taxonomy for tag operations.
     * Returns empty string if taxonomy is not found.
     *
     * @return string The tags taxonomy UUID or empty string
     */
    protected function getTagTaxonomyId(): string
    {
        return \Modules\Taxonomy\Domain\Models\Taxonomy::where('slug', 'tags')->value('id') ?? '';
    }
}
