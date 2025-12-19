<?php

declare(strict_types=1);

namespace Modules\Services\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Services\Application\Services\ServiceQueryService;
use Modules\Services\Http\Requests\CompareRevisionsRequest;
use Modules\Services\Http\Resources\ServiceResource;

/**
 * Service Listing Controller.
 *
 * Handles all read-only operations for services.
 *
 * @package Modules\Services\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ServiceListingController extends BaseController
{
    public function __construct(
        private readonly ServiceQueryService $queryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $services = $this->queryService->list(
                $request->only(['status', 'is_featured', 'category_id', 'search']),
                $request->integer('per_page', 20)
            );

            return $this->paginated(ServiceResource::collection($services)->resource);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve services: ' . $e->getMessage());
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            return $service
                ? $this->success(new ServiceResource($service))
                : $this->notFound('Service not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve service: ' . $e->getMessage());
        }
    }

    public function showBySlug(string $slug): JsonResponse
    {
        try {
            $service = $this->queryService->findBySlug($slug);

            return $service
                ? $this->success(new ServiceResource($service))
                : $this->notFound('Service not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve service: ' . $e->getMessage());
        }
    }

    public function revisions(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            return $this->success($this->queryService->getRevisions($service));
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve revisions: ' . $e->getMessage());
        }
    }

    public function compareRevisions(CompareRevisionsRequest $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $data = $request->validated();

            return $this->success($this->queryService->compareRevisions($service, $data['revision_1'], $data['revision_2']));
        } catch (\Throwable $e) {
            return $this->error('Failed to compare revisions: ' . $e->getMessage());
        }
    }
}
