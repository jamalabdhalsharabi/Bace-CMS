<?php

declare(strict_types=1);

namespace Modules\Content\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Contracts\ArticleServiceContract;
use Modules\Content\Http\Requests\CreateArticleRequest;
use Modules\Content\Http\Requests\UpdateArticleRequest;
use Modules\Content\Http\Resources\ArticleResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Class ArticleController
 * 
 * API controller for managing articles/blog posts including CRUD,
 * workflow, categories, tags, comments, revisions, and social sharing.
 * 
 * @package Modules\Content\Http\Controllers\Api
 */
class ArticleController extends BaseController
{
    /**
     * The article service instance for handling article-related business logic.
     *
     * @var ArticleServiceContract
     */
    protected ArticleServiceContract $articleService;

    /**
     * Create a new ArticleController instance.
     *
     * @param ArticleServiceContract $articleService The article service contract implementation
     */
    public function __construct(
        ArticleServiceContract $articleService
    ) {
        $this->articleService = $articleService;
    }

    /**
     * Display a paginated listing of articles.
     *
     * Supports filtering by status, type, author, featured flag, and search term.
     *
     * @param Request $request The incoming HTTP request containing filter parameters
     * @return JsonResponse Paginated list of articles wrapped in ArticleResource
     */
    public function index(Request $request): JsonResponse
    {
        $articles = $this->articleService->list(
            filters: $request->only(['status', 'type', 'author_id', 'featured', 'search']),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginated(ArticleResource::collection($articles)->resource);
    }

    /**
     * Display the specified article by its UUID.
     *
     * @param string $id The UUID of the article to retrieve
     * @return JsonResponse The article data wrapped in ArticleResource or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        return $this->success(new ArticleResource($article));
    }

    /**
     * Display the specified article by its URL slug.
     *
     * Also increments the article's view count for analytics.
     *
     * @param string $slug The URL-friendly slug of the article
     * @return JsonResponse The article data wrapped in ArticleResource or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $article = $this->articleService->findBySlug($slug);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article->incrementViewCount();

        return $this->success(new ArticleResource($article));
    }

    /**
     * Store a newly created article in the database.
     *
     * @param CreateArticleRequest $request The validated request containing article data
     * @return JsonResponse The newly created article wrapped in ArticleResource (HTTP 201)
     */
    public function store(CreateArticleRequest $request): JsonResponse
    {
        $article = $this->articleService->create($request->validated());

        return $this->created(new ArticleResource($article), 'Article created successfully');
    }

    /**
     * Update the specified article in the database.
     *
     * @param UpdateArticleRequest $request The validated request containing updated article data
     * @param string $id The UUID of the article to update
     * @return JsonResponse The updated article wrapped in ArticleResource or 404 error
     */
    public function update(UpdateArticleRequest $request, string $id): JsonResponse
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->articleService->update($article, $request->validated());

        return $this->success(new ArticleResource($article), 'Article updated successfully');
    }

    /**
     * Soft delete the specified article.
     *
     * The article will be moved to trash and can be restored later.
     *
     * @param string $id The UUID of the article to delete
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $this->articleService->delete($article);

        return $this->success(null, 'Article deleted successfully');
    }

    /**
     * Publish the specified article, making it publicly visible.
     *
     * @param string $id The UUID of the article to publish
     * @return JsonResponse The published article wrapped in ArticleResource or 404 error
     */
    public function publish(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->articleService->publish($article);

        return $this->success(new ArticleResource($article), 'Article published successfully');
    }

    /**
     * Unpublish a currently published article.
     *
     * Removes the article from public view.
     *
     * @param string $id The UUID of the article to unpublish
     * @return JsonResponse The unpublished article or 404 error
     */
    public function unpublish(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->articleService->unpublish($article);

        return $this->success(new ArticleResource($article), 'Article unpublished successfully');
    }

    /**
     * Schedule an article for future publication.
     *
     * @param Request $request The request containing 'publish_at' datetime
     * @param string $id The UUID of the article to schedule
     * @return JsonResponse The scheduled article or 404 error
     */
    public function schedule(Request $request, string $id): JsonResponse
    {
        $request->validate(['publish_at' => 'required|date|after:now']);

        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->articleService->schedule($article, Carbon::parse($request->publish_at));

        return $this->success(new ArticleResource($article), 'Article scheduled successfully');
    }

    /**
     * Create a duplicate copy of the article.
     *
     * @param string $id The UUID of the article to duplicate
     * @return JsonResponse The duplicated article (HTTP 201) or 404 error
     */
    public function duplicate(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->created(new ArticleResource($this->articleService->duplicate($article)));
    }

    /**
     * Permanently delete the specified article from the database.
     *
     * @param string $id The UUID of the article to permanently delete
     * @return JsonResponse Success message or 404 error
     */
    public function forceDestroy(string $id): JsonResponse
    {
        $article = \Modules\Content\Domain\Models\Article::withTrashed()->find($id);
        if (!$article) return $this->notFound('Article not found');
        $this->articleService->forceDelete($article);
        return $this->success(null, 'Article permanently deleted');
    }

    /**
     * Restore a soft-deleted article from the trash.
     *
     * @param string $id The UUID of the article to restore
     * @return JsonResponse The restored article or 404 error
     */
    public function restore(string $id): JsonResponse
    {
        $article = $this->articleService->restore($id);
        return $article ? $this->success(new ArticleResource($article)) : $this->notFound('Article not found');
    }

    /**
     * Submit the article for editorial review.
     *
     * @param string $id The UUID of the article to submit
     * @return JsonResponse The updated article or 404 error
     */
    public function submitForReview(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->submitForReview($article)));
    }

    /**
     * Start the review process for an article.
     *
     * @param string $id The UUID of the article
     * @return JsonResponse The updated article or 404 error
     */
    public function startReview(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->startReview($article)));
    }

    /**
     * Approve an article that is pending review.
     *
     * @param Request $request The request containing optional approval notes
     * @param string $id The UUID of the article to approve
     * @return JsonResponse The approved article or 404 error
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->approve($article, $request->notes)));
    }

    /**
     * Reject an article that is pending review.
     *
     * @param Request $request The request containing rejection notes
     * @param string $id The UUID of the article to reject
     * @return JsonResponse The rejected article or 404 error
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->reject($article, $request->notes)));
    }

    /**
     * Archive the specified article.
     *
     * @param string $id The UUID of the article to archive
     * @return JsonResponse The archived article or 404 error
     */
    public function archive(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->archive($article)));
    }

    /**
     * Restore an archived article back to active status.
     *
     * @param string $id The UUID of the article to unarchive
     * @return JsonResponse The restored article or 404 error
     */
    public function unarchive(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->unarchive($article)));
    }

    /**
     * Pin the article to the top of listings.
     *
     * @param string $id The UUID of the article to pin
     * @return JsonResponse The pinned article or 404 error
     */
    public function pin(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->pin($article)));
    }

    /**
     * Remove the pin from the article.
     *
     * @param string $id The UUID of the article to unpin
     * @return JsonResponse The unpinned article or 404 error
     */
    public function unpin(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->unpin($article)));
    }

    /**
     * Sync categories for the article.
     *
     * Replaces all existing category associations.
     *
     * @param Request $request The request containing 'category_ids' array
     * @param string $id The UUID of the article
     * @return JsonResponse The updated article or 404 error
     */
    public function syncCategories(Request $request, string $id): JsonResponse
    {
        $request->validate(['category_ids' => 'required|array']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->syncCategories($article, $request->category_ids)));
    }

    /**
     * Add tags to the article.
     *
     * @param Request $request The request containing 'tags' array
     * @param string $id The UUID of the article
     * @return JsonResponse The updated article or 404 error
     */
    public function addTags(Request $request, string $id): JsonResponse
    {
        $request->validate(['tags' => 'required|array']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->addTags($article, $request->tags)));
    }

    /**
     * Remove tags from the article.
     *
     * @param Request $request The request containing 'tag_ids' array
     * @param string $id The UUID of the article
     * @return JsonResponse The updated article or 404 error
     */
    public function removeTags(Request $request, string $id): JsonResponse
    {
        $request->validate(['tag_ids' => 'required|array']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->removeTags($article, $request->tag_ids)));
    }

    /**
     * Attach related articles to this article.
     *
     * @param Request $request The request containing 'article_ids' array
     * @param string $id The UUID of the article
     * @return JsonResponse The updated article or 404 error
     */
    public function attachRelated(Request $request, string $id): JsonResponse
    {
        $request->validate(['article_ids' => 'required|array']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->attachRelated($article, $request->article_ids)));
    }

    /**
     * Enable comments on the article.
     *
     * @param string $id The UUID of the article
     * @return JsonResponse The updated article or 404 error
     */
    public function enableComments(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->enableComments($article)));
    }

    /**
     * Disable comments on the article.
     *
     * @param string $id The UUID of the article
     * @return JsonResponse The updated article or 404 error
     */
    public function disableComments(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->disableComments($article)));
    }

    /**
     * Close comments on the article (no new comments allowed).
     *
     * @param string $id The UUID of the article
     * @return JsonResponse The updated article or 404 error
     */
    public function closeComments(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->closeComments($article)));
    }

    /**
     * Get the revision history of the article.
     *
     * @param string $id The UUID of the article
     * @return JsonResponse Array of revisions or 404 error
     */
    public function revisions(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success($this->articleService->getRevisions($article));
    }

    /**
     * Restore the article to a previous revision.
     *
     * @param Request $request The request containing 'revision_number'
     * @param string $id The UUID of the article
     * @return JsonResponse The restored article or 404 error
     */
    public function restoreRevision(Request $request, string $id): JsonResponse
    {
        $request->validate(['revision_number' => 'required|integer']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->restoreRevision($article, $request->revision_number)));
    }

    /**
     * Share the article on social media platforms.
     *
     * @param Request $request The request containing 'platforms' array
     * @param string $id The UUID of the article
     * @return JsonResponse Share results or 404 error
     */
    public function shareOnSocial(Request $request, string $id): JsonResponse
    {
        $request->validate(['platforms' => 'required|array']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success($this->articleService->shareOnSocial($article, $request->platforms));
    }

    /**
     * Queue the article to be sent to newsletter subscribers.
     *
     * @param string $id The UUID of the article
     * @return JsonResponse Success message or 404 error
     */
    public function sendToNewsletter(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        $this->articleService->sendToNewsletter($article);
        return $this->success(null, 'Queued for newsletter');
    }

    /**
     * Get analytics data for the article.
     *
     * Returns view counts, engagement metrics, and other statistics.
     *
     * @param string $id The UUID of the article
     * @return JsonResponse Analytics data or 404 error
     */
    public function analytics(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success($this->articleService->getAnalytics($article));
    }
}
