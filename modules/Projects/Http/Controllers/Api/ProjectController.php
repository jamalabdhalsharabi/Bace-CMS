<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Projects\Application\Services\ProjectCommandService;
use Modules\Projects\Application\Services\ProjectQueryService;
use Modules\Projects\Http\Requests\CreateProjectRequest;
use Modules\Projects\Http\Requests\UpdateProjectRequest;
use Modules\Projects\Http\Resources\ProjectResource;

class ProjectController extends BaseController
{
    public function __construct(
        protected ProjectQueryService $queryService,
        protected ProjectCommandService $commandService
    ) {
    }

    /**
     * Display a paginated listing of portfolio projects.
     *
     * Supports filtering by status, featured flag, and type.
     *
     * @param Request $request The incoming HTTP request containing filter parameters
     * @return JsonResponse Paginated list of projects wrapped in ProjectResource
     */
    public function index(Request $request): JsonResponse
    {
        $projects = $this->queryService->list(
            $request->only(['status', 'featured', 'type']),
            $request->integer('per_page', 12)
        );
        return $this->paginated(ProjectResource::collection($projects)->resource);
    }

    /**
     * Display the specified project by its UUID.
     *
     * @param string $id The UUID of the project to retrieve
     * @return JsonResponse The project data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        return $this->success(new ProjectResource($project));
    }

    /**
     * Display the specified project by its URL slug.
     *
     * @param string $slug The URL-friendly slug of the project
     * @return JsonResponse The project data or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $project = $this->queryService->findBySlug($slug);
        if (!$project) return $this->notFound('Project not found');
        return $this->success(new ProjectResource($project));
    }

    /**
     * Store a newly created project in the database.
     *
     * @param CreateProjectRequest $request The validated request containing project data
     * @return JsonResponse The newly created project (HTTP 201)
     */
    public function store(CreateProjectRequest $request): JsonResponse
    {
        $project = $this->queryService->create($request->validated());
        return $this->created(new ProjectResource($project));
    }

    /**
     * Update the specified project in the database.
     *
     * @param UpdateProjectRequest $request The validated request containing updated data
     * @param string $id The UUID of the project to update
     * @return JsonResponse The updated project or 404 error
     */
    public function update(UpdateProjectRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->queryService->update($project, $request->validated());
        return $this->success(new ProjectResource($project));
    }

    /**
     * Delete the specified project.
     *
     * @param string $id The UUID of the project to delete
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->queryService->delete($project);
        return $this->success(null, 'Project deleted');
    }

    /**
     * Publish the specified project, making it publicly visible.
     *
     * @param string $id The UUID of the project to publish
     * @return JsonResponse The published project or 404 error
     */
    public function publish(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->queryService->publish($project);
        return $this->success(new ProjectResource($project));
    }
}
