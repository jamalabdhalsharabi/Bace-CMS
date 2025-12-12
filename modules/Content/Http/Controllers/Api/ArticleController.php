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

class ArticleController extends BaseController
{
    public function __construct(
        protected ArticleServiceContract $articleService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $articles = $this->articleService->list(
            filters: $request->only(['status', 'type', 'author_id', 'featured', 'search']),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginated(ArticleResource::collection($articles)->resource);
    }

    public function show(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        return $this->success(new ArticleResource($article));
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $article = $this->articleService->findBySlug($slug);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article->incrementViewCount();

        return $this->success(new ArticleResource($article));
    }

    public function store(CreateArticleRequest $request): JsonResponse
    {
        $article = $this->articleService->create($request->validated());

        return $this->created(new ArticleResource($article), 'Article created successfully');
    }

    public function update(UpdateArticleRequest $request, string $id): JsonResponse
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->articleService->update($article, $request->validated());

        return $this->success(new ArticleResource($article), 'Article updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $this->articleService->delete($article);

        return $this->success(null, 'Article deleted successfully');
    }

    public function publish(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->articleService->publish($article);

        return $this->success(new ArticleResource($article), 'Article published successfully');
    }

    public function unpublish(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);

        if (!$article) {
            return $this->notFound('Article not found');
        }

        $article = $this->articleService->unpublish($article);

        return $this->success(new ArticleResource($article), 'Article unpublished successfully');
    }

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

    public function duplicate(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->created(new ArticleResource($this->articleService->duplicate($article)));
    }

    public function forceDestroy(string $id): JsonResponse
    {
        $article = \Modules\Content\Domain\Models\Article::withTrashed()->find($id);
        if (!$article) return $this->notFound('Article not found');
        $this->articleService->forceDelete($article);
        return $this->success(null, 'Article permanently deleted');
    }

    public function restore(string $id): JsonResponse
    {
        $article = $this->articleService->restore($id);
        return $article ? $this->success(new ArticleResource($article)) : $this->notFound('Article not found');
    }

    public function submitForReview(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->submitForReview($article)));
    }

    public function startReview(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->startReview($article)));
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->approve($article, $request->notes)));
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->reject($article, $request->notes)));
    }

    public function archive(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->archive($article)));
    }

    public function unarchive(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->unarchive($article)));
    }

    public function pin(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->pin($article)));
    }

    public function unpin(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->unpin($article)));
    }

    public function syncCategories(Request $request, string $id): JsonResponse
    {
        $request->validate(['category_ids' => 'required|array']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->syncCategories($article, $request->category_ids)));
    }

    public function addTags(Request $request, string $id): JsonResponse
    {
        $request->validate(['tags' => 'required|array']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->addTags($article, $request->tags)));
    }

    public function removeTags(Request $request, string $id): JsonResponse
    {
        $request->validate(['tag_ids' => 'required|array']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->removeTags($article, $request->tag_ids)));
    }

    public function attachRelated(Request $request, string $id): JsonResponse
    {
        $request->validate(['article_ids' => 'required|array']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->attachRelated($article, $request->article_ids)));
    }

    public function enableComments(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->enableComments($article)));
    }

    public function disableComments(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->disableComments($article)));
    }

    public function closeComments(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->closeComments($article)));
    }

    public function revisions(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success($this->articleService->getRevisions($article));
    }

    public function restoreRevision(Request $request, string $id): JsonResponse
    {
        $request->validate(['revision_number' => 'required|integer']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success(new ArticleResource($this->articleService->restoreRevision($article, $request->revision_number)));
    }

    public function shareOnSocial(Request $request, string $id): JsonResponse
    {
        $request->validate(['platforms' => 'required|array']);
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success($this->articleService->shareOnSocial($article, $request->platforms));
    }

    public function sendToNewsletter(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        $this->articleService->sendToNewsletter($article);
        return $this->success(null, 'Queued for newsletter');
    }

    public function analytics(string $id): JsonResponse
    {
        $article = $this->articleService->find($id);
        if (!$article) return $this->notFound('Article not found');
        return $this->success($this->articleService->getAnalytics($article));
    }
}
