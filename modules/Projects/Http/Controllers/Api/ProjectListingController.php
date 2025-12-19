<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Projects\Application\Services\ProjectQueryService;
use Modules\Projects\Http\Resources\ProjectResource;

/**
 * Project Listing Controller.
 *
 * Handles all read-only operations for projects.
 *
 * @package Modules\Projects\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ProjectListingController extends BaseController
{
    /**
     * @param ProjectQueryService $queryService Service for project read operations
     */
    public function __construct(
        private readonly ProjectQueryService $queryService
    ) {}

    /**
     * Display a paginated listing of projects.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $projects = $this->queryService->list(
                $request->only(['status', 'featured', 'type']),
                $request->integer('per_page', 12)
            );

            return $this->paginated(ProjectResource::collection($projects)->resource);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve projects: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified project by its UUID.
     */
    public function show(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            return $project
                ? $this->success(new ProjectResource($project))
                : $this->notFound('Project not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve project: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified project by its URL slug.
     */
    public function showBySlug(string $slug): JsonResponse
    {
        try {
            $project = $this->queryService->findBySlug($slug);

            return $project
                ? $this->success(new ProjectResource($project))
                : $this->notFound('Project not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve project: ' . $e->getMessage());
        }
    }

    /**
     * Get featured projects.
     */
    public function featured(Request $request): JsonResponse
    {
        try {
            $projects = $this->queryService->getFeatured($request->integer('limit', 6));

            return $this->success(ProjectResource::collection($projects));
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve featured projects: ' . $e->getMessage());
        }
    }

    /**
     * Get revision history.
     */
    public function revisions(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $revisions = $this->queryService->getRevisions($project);

            return $this->success($revisions);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve revisions: ' . $e->getMessage());
        }
    }
}
