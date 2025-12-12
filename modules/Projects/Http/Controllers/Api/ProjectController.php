<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Projects\Contracts\ProjectServiceContract;
use Modules\Projects\Http\Requests\CreateProjectRequest;
use Modules\Projects\Http\Requests\UpdateProjectRequest;
use Modules\Projects\Http\Resources\ProjectResource;

class ProjectController extends BaseController
{
    public function __construct(
        protected ProjectServiceContract $projectService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $projects = $this->projectService->list(
            $request->only(['status', 'featured', 'type']),
            $request->integer('per_page', 12)
        );
        return $this->paginated(ProjectResource::collection($projects)->resource);
    }

    public function show(string $id): JsonResponse
    {
        $project = $this->projectService->find($id);
        if (!$project) return $this->notFound('Project not found');
        return $this->success(new ProjectResource($project));
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $project = $this->projectService->findBySlug($slug);
        if (!$project) return $this->notFound('Project not found');
        return $this->success(new ProjectResource($project));
    }

    public function store(CreateProjectRequest $request): JsonResponse
    {
        $project = $this->projectService->create($request->validated());
        return $this->created(new ProjectResource($project));
    }

    public function update(UpdateProjectRequest $request, string $id): JsonResponse
    {
        $project = $this->projectService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->projectService->update($project, $request->validated());
        return $this->success(new ProjectResource($project));
    }

    public function destroy(string $id): JsonResponse
    {
        $project = $this->projectService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->projectService->delete($project);
        return $this->success(null, 'Project deleted');
    }

    public function publish(string $id): JsonResponse
    {
        $project = $this->projectService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->projectService->publish($project);
        return $this->success(new ProjectResource($project));
    }
}
