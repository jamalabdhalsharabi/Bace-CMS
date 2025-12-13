<?php

declare(strict_types=1);

namespace Modules\Services\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Services\Contracts\ServiceServiceContract;
use Modules\Services\Http\Resources\ServiceResource;

/**
 * Class ServiceController
 *
 * API controller for managing services including CRUD,
 * workflow, translations, media, categories, and revisions.
 *
 * @package Modules\Services\Http\Controllers\Api
 */
class ServiceController extends BaseController
{
    /**
     * The service service instance.
     *
     * @var ServiceServiceContract
     */
    protected ServiceServiceContract $serviceService;

    /**
     * Create a new ServiceController instance.
     *
     * @param ServiceServiceContract $serviceService The service implementation
     */
    public function __construct(ServiceServiceContract $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    /**
     * Display a paginated listing of services.
     *
     * @param Request $request The request with optional filters
     * @return JsonResponse Paginated list of services
     */
    public function index(Request $request): JsonResponse
    {
        $services = $this->serviceService->list(
            $request->only(['status', 'is_featured', 'category_id', 'search']),
            $request->integer('per_page', 20)
        );
        return $this->paginated(ServiceResource::collection($services)->resource);
    }

    /**
     * Display the specified service by its UUID.
     *
     * @param string $id The UUID of the service
     * @return JsonResponse The service or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        return $service ? $this->success(new ServiceResource($service)) : $this->notFound('Service not found');
    }

    /**
     * Display the specified service by its slug.
     *
     * @param string $slug The URL-friendly slug
     * @return JsonResponse The service or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $service = $this->serviceService->findBySlug($slug);
        return $service ? $this->success(new ServiceResource($service)) : $this->notFound('Service not found');
    }

    /**
     * Store a newly created service.
     *
     * @param Request $request The request with service data
     * @return JsonResponse The created service (HTTP 201)
     */
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

    /**
     * Update the specified service.
     *
     * @param Request $request The request with updated data
     * @param string $id The UUID of the service
     * @return JsonResponse The updated service or 404 error
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->update($service, $request->all())));
    }

    /**
     * Soft delete the specified service.
     *
     * @param string $id The UUID of the service
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        $this->serviceService->delete($service);
        return $this->success(null, 'Service deleted');
    }

    /**
     * Permanently delete the specified service.
     *
     * @param string $id The UUID of the service
     * @return JsonResponse Success message or 404 error
     */
    public function forceDestroy(string $id): JsonResponse
    {
        $service = \Modules\Services\Domain\Models\Service::withTrashed()->find($id);
        if (!$service) return $this->notFound('Service not found');
        $this->serviceService->forceDelete($service);
        return $this->success(null, 'Service permanently deleted');
    }

    /**
     * Restore a soft-deleted service.
     *
     * @param string $id The UUID of the service
     * @return JsonResponse The restored service or 404 error
     */
    public function restore(string $id): JsonResponse
    {
        $service = $this->serviceService->restore($id);
        return $service ? $this->success(new ServiceResource($service)) : $this->notFound('Service not found');
    }

    /**
     * Save the service as a draft.
     *
     * @param Request $request The request with draft data
     * @param string $id The UUID of the service
     * @return JsonResponse The updated service or 404 error
     */
    public function saveDraft(Request $request, string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->saveDraft($service, $request->all())));
    }

    /**
     * Submit the service for review.
     *
     * @param string $id The UUID of the service
     * @return JsonResponse The updated service or 404 error
     */
    public function submitForReview(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->submitForReview($service)));
    }

    /**
     * Start the review process.
     *
     * @param string $id The UUID of the service
     * @return JsonResponse The updated service or 404 error
     */
    public function startReview(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->startReview($service, auth()->id())));
    }

    /**
     * Approve the service.
     *
     * @param Request $request The request with optional notes
     * @param string $id The UUID of the service
     * @return JsonResponse The approved service or 404 error
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->approve($service, $request->notes)));
    }

    /**
     * Reject the service.
     *
     * @param Request $request The request with rejection notes
     * @param string $id The UUID of the service
     * @return JsonResponse The rejected service or 404 error
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->reject($service, $request->notes)));
    }

    /**
     * Publish the service.
     *
     * @param string $id The UUID of the service
     * @return JsonResponse The published service or 404 error
     */
    public function publish(string $id): JsonResponse
    {
        $service = $this->serviceService->find($id);
        if (!$service) return $this->notFound('Service not found');
        return $this->success(new ServiceResource($this->serviceService->publish($service)));
    }

    /**
     * Schedule the service for future publication.
     *
     * @param Request $request The request with scheduled_at date
     * @param string $id The UUID of the service
     * @return JsonResponse The scheduled service or 404 error
     */
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
