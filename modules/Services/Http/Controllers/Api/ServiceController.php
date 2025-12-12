<?php

declare(strict_types=1);

namespace Modules\Services\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Services\Contracts\ServiceServiceContract;
use Modules\Services\Http\Resources\ServiceResource;

class ServiceController extends BaseController
{
    public function __construct(protected ServiceServiceContract $serviceService) {}

    public function index(Request $request): JsonResponse
    {
        $services = $this->serviceService->list(
            $request->only(['status', 'is_featured', 'category_id', 'search']),
            $request->integer('per_page', 20)
        );
        return $this->paginated(ServiceResource::collection($services)->resource);
    }

    public function show(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        return $service ? $this->success(new ServiceResource($service)) : $this->notFound('Service not found');
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $service = $this->serviceService->findBySlug($slug);
        return $service ? $this->success(new ServiceResource($service)) : $this->notFound('Service not found');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'slug' => 'required|string|max:100|unique:services,slug',
            'translations' => 'required|array|min:1',
            'translations.*.name' => 'required|string|max:200',
            'category_ids' => 'nullable|array',
        ]);
        return $this->created(new ServiceResource($this->serviceService->create($request->all())));
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->update($service, $request->all())));
    }

    public function destroy(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        $this->serviceService->delete($service);
        return $this->success(null, 'Service deleted');
    }

    public function forceDestroy(string $id): JsonResponse
    {
        $service = \Modules\Services\Domain\Models\Service::withTrashed()->find($id);
        if (!$service) return $this->notFound('Service not found');
        $this->serviceService->forceDelete($service);
        return $this->success(null, 'Service permanently deleted');
    }

    public function restore(string $id): JsonResponse
    {
        $service = $this->serviceService->restore($id);
        return $service ? $this->success(new ServiceResource($service)) : $this->notFound('Service not found');
    }

    // Workflow endpoints
    public function saveDraft(Request $request, string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->saveDraft($service, $request->all())));
    }

    public function submitForReview(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->submitForReview($service)));
    }

    public function startReview(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->startReview($service, auth()->id())));
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->approve($service, $request->notes)));
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->reject($service, $request->notes)));
    }

    public function publish(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->publish($service)));
    }

    public function schedule(Request $request, string $id): JsonResponse
    {
        $request->validate(['scheduled_at' => 'required|date|after:now']);
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->schedule($service, new \DateTime($request->scheduled_at))));
    }

    public function cancelSchedule(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->cancelSchedule($service)));
    }

    public function unpublish(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->unpublish($service)));
    }

    public function archive(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->archive($service)));
    }

    public function unarchive(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->unarchive($service)));
    }

    // Features
    public function feature(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->feature($service)));
    }

    public function unfeature(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->unfeature($service)));
    }

    public function clone(Request $request, string $id): JsonResponse
    {
        $request->validate(['new_slug' => 'required|string|max:100|unique:services,slug']);
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->created(new ServiceResource($this->serviceService->clone($service, $request->new_slug)));
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'uuid']);
        $this->serviceService->reorder($request->order);
        return $this->success(null, 'Services reordered');
    }

    // Translations
    public function createTranslation(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'locale' => 'required|string|max:10',
            'name' => 'required|string|max:200',
        ]);
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->createTranslation($service, $request->locale, $request->except('locale'))));
    }

    // Media
    public function attachMedia(Request $request, string $id): JsonResponse
    {
        $request->validate(['media_ids' => 'required|array', 'media_ids.*' => 'uuid']);
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->attachMedia($service, $request->media_ids)));
    }

    public function detachMedia(Request $request, string $id): JsonResponse
    {
        $request->validate(['media_ids' => 'required|array', 'media_ids.*' => 'uuid']);
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->detachMedia($service, $request->media_ids)));
    }

    public function reorderMedia(Request $request, string $id): JsonResponse
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'uuid']);
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->reorderMedia($service, $request->order)));
    }

    // Categories
    public function syncCategories(Request $request, string $id): JsonResponse
    {
        $request->validate(['term_ids' => 'required|array']);
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->syncCategories($service, $request->term_ids)));
    }

    public function attachRelated(Request $request, string $id): JsonResponse
    {
        $request->validate(['service_ids' => 'required|array', 'service_ids.*' => 'uuid']);
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->attachRelated($service, $request->service_ids)));
    }

    // Revisions
    public function revisions(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success($this->serviceService->getRevisions($service));
    }

    public function compareRevisions(Request $request, string $id): JsonResponse
    {
        $request->validate(['revision_1' => 'required|uuid', 'revision_2' => 'required|uuid']);
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success($this->serviceService->compareRevisions($service, $request->revision_1, $request->revision_2));
    }

    public function restoreRevision(Request $request, string $id): JsonResponse
    {
        $request->validate(['revision_id' => 'required|uuid']);
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->restoreRevision($service, $request->revision_id)));
    }

    // Search
    public function indexInSearch(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        $this->serviceService->indexInSearch($service);
        return $this->success(null, 'Service indexed');
    }

    public function removeFromIndex(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        $this->serviceService->removeFromIndex($service);
        return $this->success(null, 'Service removed from index');
    }
}
