<?php

declare(strict_types=1);

namespace Modules\Content\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Contracts\PageServiceContract;
use Modules\Content\Http\Requests\CreatePageRequest;
use Modules\Content\Http\Requests\UpdatePageRequest;
use Modules\Content\Http\Resources\PageResource;
use Modules\Core\Http\Controllers\BaseController;

class PageController extends BaseController
{
    public function __construct(
        protected PageServiceContract $pageService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $pages = $this->pageService->list(
            filters: $request->only(['status', 'parent_id', 'root', 'search']),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginated(PageResource::collection($pages)->resource);
    }

    public function tree(): JsonResponse
    {
        $pages = $this->pageService->getTree();

        return $this->success(PageResource::collection($pages));
    }

    public function show(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);

        if (!$page) {
            return $this->notFound('Page not found');
        }

        return $this->success(new PageResource($page));
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $page = $this->pageService->findBySlug($slug);

        if (!$page) {
            return $this->notFound('Page not found');
        }

        return $this->success(new PageResource($page));
    }

    public function store(CreatePageRequest $request): JsonResponse
    {
        $page = $this->pageService->create($request->validated());

        return $this->created(new PageResource($page), 'Page created successfully');
    }

    public function update(UpdatePageRequest $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);

        if (!$page) {
            return $this->notFound('Page not found');
        }

        $page = $this->pageService->update($page, $request->validated());

        return $this->success(new PageResource($page), 'Page updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);

        if (!$page) {
            return $this->notFound('Page not found');
        }

        $this->pageService->delete($page);

        return $this->success(null, 'Page deleted successfully');
    }

    public function publish(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);

        if (!$page) {
            return $this->notFound('Page not found');
        }

        $page = $this->pageService->publish($page);

        return $this->success(new PageResource($page), 'Page published successfully');
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'uuid']);

        $this->pageService->reorder($request->order);

        return $this->success(null, 'Pages reordered successfully');
    }

    public function setHomepage(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->setAsHomepage($page)), 'Homepage set');
    }

    public function forceDestroy(string $id): JsonResponse
    {
        $page = \Modules\Content\Domain\Models\Page::withTrashed()->find($id);
        if (!$page) return $this->notFound('Page not found');
        $this->pageService->forceDelete($page);
        return $this->success(null, 'Page permanently deleted');
    }

    public function restore(string $id): JsonResponse
    {
        $page = $this->pageService->restore($id);
        return $page ? $this->success(new PageResource($page)) : $this->notFound('Page not found');
    }

    public function saveDraft(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->saveDraft($page, $request->all())));
    }

    public function submitForReview(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->submitForReview($page)));
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->approve($page, $request->notes)));
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->reject($page, $request->notes)));
    }

    public function schedule(Request $request, string $id): JsonResponse
    {
        $request->validate(['scheduled_at' => 'required|date|after:now']);
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->schedule($page, new \DateTime($request->scheduled_at))));
    }

    public function cancelSchedule(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->cancelSchedule($page)));
    }

    public function unpublish(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->unpublish($page)));
    }

    public function archive(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->archive($page)));
    }

    public function unarchive(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->unarchive($page)));
    }

    public function move(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->move($page, $request->parent_id)));
    }

    public function set404(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->setAs404($page)));
    }

    public function addSection(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->addSection($page, $request->all())));
    }

    public function updateSection(Request $request, string $id, string $sectionId): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->updateSection($page, $sectionId, $request->all())));
    }

    public function deleteSection(string $id, string $sectionId): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->deleteSection($page, $sectionId)));
    }

    public function reorderSections(Request $request, string $id): JsonResponse
    {
        $request->validate(['order' => 'required|array']);
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->reorderSections($page, $request->order)));
    }

    public function changeTemplate(Request $request, string $id): JsonResponse
    {
        $request->validate(['template' => 'required|string']);
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->changeTemplate($page, $request->template)));
    }

    public function lock(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->lock($page)));
    }

    public function unlock(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->unlock($page)));
    }

    public function duplicate(Request $request, string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->created(new PageResource($this->pageService->duplicate($page, $request->new_slug)));
    }

    public function preview(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success($this->pageService->preview($page));
    }

    public function revisions(string $id): JsonResponse
    {
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success($this->pageService->getRevisions($page));
    }

    public function restoreRevision(Request $request, string $id): JsonResponse
    {
        $request->validate(['revision_number' => 'required|integer']);
        $page = $this->pageService->find($id);
        if (!$page) return $this->notFound('Page not found');
        return $this->success(new PageResource($this->pageService->restoreRevision($page, $request->revision_number)));
    }
}
