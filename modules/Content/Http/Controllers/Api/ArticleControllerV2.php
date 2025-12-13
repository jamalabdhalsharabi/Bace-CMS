<?php

declare(strict_types=1);

namespace Modules\Content\Http\Controllers\Api;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Application\Services\ArticleCommandService;
use Modules\Content\Application\Services\ArticleCommentService;
use Modules\Content\Application\Services\ArticleQueryService;
use Modules\Content\Application\Services\ArticleTaxonomyService;
use Modules\Content\Application\Services\ArticleWorkflowService;
use Modules\Content\Domain\DTO\ArticleData;
use Modules\Content\Http\Requests\CreateArticleRequest;
use Modules\Content\Http\Requests\UpdateArticleRequest;
use Modules\Content\Http\Resources\ArticleResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Article Controller V2.
 *
 * Uses the new Clean Architecture with separated services.
 * Each service has a single responsibility.
 */
final class ArticleControllerV2 extends BaseController
{
    public function __construct(
        private readonly ArticleQueryService $queryService,
        private readonly ArticleCommandService $commandService,
        private readonly ArticleWorkflowService $workflowService,
        private readonly ArticleTaxonomyService $taxonomyService,
        private readonly ArticleCommentService $commentService,
    ) {}

    /**
     * List articles with filters.
     */
    public function index(Request $request): JsonResponse
    {
        $articles = $this->queryService->list(
            filters: $request->only(['status', 'type', 'author_id', 'featured', 'search']),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginated(ArticleResource::collection($articles)->resource);
    }

    /**
     * Show a single article.
     */
    public function show(string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        return $this->success(new ArticleResource($article));
    }

    /**
     * Show article by slug.
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $article = $this->queryService->findBySlug($slug);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        return $this->success(new ArticleResource($article));
    }

    /**
     * Create a new article.
     */
    public function store(CreateArticleRequest $request): JsonResponse
    {
        $data = ArticleData::fromRequest($request);
        $article = $this->commandService->create($data);

        return $this->created(new ArticleResource($article), 'Article created');
    }

    /**
     * Update an article.
     */
    public function update(UpdateArticleRequest $request, string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $data = ArticleData::fromRequest($request);
        $article = $this->commandService->update($article, $data);

        return $this->success(new ArticleResource($article), 'Article updated');
    }

    /**
     * Delete an article.
     */
    public function destroy(string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $this->commandService->delete($article);

        return $this->success(null, 'Article deleted');
    }

    /**
     * Publish an article.
     */
    public function publish(string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->commandService->publish($article);

        return $this->success(new ArticleResource($article), 'Article published');
    }

    /**
     * Unpublish an article.
     */
    public function unpublish(string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->commandService->unpublish($article);

        return $this->success(new ArticleResource($article), 'Article unpublished');
    }

    /**
     * Schedule an article.
     */
    public function schedule(Request $request, string $id): JsonResponse
    {
        $request->validate(['publish_at' => 'required|date|after:now']);

        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->commandService->schedule($article, Carbon::parse($request->publish_at));

        return $this->success(new ArticleResource($article), 'Article scheduled');
    }

    /**
     * Duplicate an article.
     */
    public function duplicate(string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $clone = $this->commandService->duplicate($article);

        return $this->created(new ArticleResource($clone), 'Article duplicated');
    }

    /**
     * Restore a deleted article.
     */
    public function restore(string $id): JsonResponse
    {
        $article = $this->commandService->restore($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        return $this->success(new ArticleResource($article), 'Article restored');
    }

    /**
     * Submit for review.
     */
    public function submitForReview(string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->workflowService->submitForReview($article);

        return $this->success(new ArticleResource($article), 'Submitted for review');
    }

    /**
     * Start review.
     */
    public function startReview(string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->workflowService->startReview($article);

        return $this->success(new ArticleResource($article), 'Review started');
    }

    /**
     * Approve article.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->workflowService->approve($article, $request->notes);

        return $this->success(new ArticleResource($article), 'Article approved');
    }

    /**
     * Reject article.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->workflowService->reject($article, $request->notes);

        return $this->success(new ArticleResource($article), 'Article rejected');
    }

    /**
     * Archive article.
     */
    public function archive(string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->workflowService->archive($article);

        return $this->success(new ArticleResource($article), 'Article archived');
    }

    /**
     * Unarchive article.
     */
    public function unarchive(string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->workflowService->unarchive($article);

        return $this->success(new ArticleResource($article), 'Article unarchived');
    }

    /**
     * Sync categories.
     */
    public function syncCategories(Request $request, string $id): JsonResponse
    {
        $request->validate(['category_ids' => 'required|array']);

        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->taxonomyService->syncCategories($article, $request->category_ids);

        return $this->success(new ArticleResource($article), 'Categories synced');
    }

    /**
     * Add tags.
     */
    public function addTags(Request $request, string $id): JsonResponse
    {
        $request->validate(['tags' => 'required|array']);

        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->taxonomyService->addTags($article, $request->tags);

        return $this->success(new ArticleResource($article), 'Tags added');
    }

    /**
     * Enable comments.
     */
    public function enableComments(string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->commentService->enableComments($article);

        return $this->success(new ArticleResource($article), 'Comments enabled');
    }

    /**
     * Disable comments.
     */
    public function disableComments(string $id): JsonResponse
    {
        $article = $this->queryService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->commentService->disableComments($article);

        return $this->success(new ArticleResource($article), 'Comments disabled');
    }
}
