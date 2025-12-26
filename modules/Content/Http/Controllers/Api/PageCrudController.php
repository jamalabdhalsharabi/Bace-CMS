<?php

declare(strict_types=1);

namespace Modules\Content\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Content\Application\Services\PageCommandService;
use Modules\Content\Application\Services\PageQueryService;
use Modules\Content\Http\Requests\CreatePageRequest;
use Modules\Content\Http\Requests\UpdatePageRequest;
use Modules\Content\Http\Resources\PageResource;
use Modules\Core\Http\Controllers\BaseController;

/**
 * Page CRUD Controller.
 *
 * Handles basic CRUD operations for pages.
 *
 * @package Modules\Content\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class PageCrudController extends BaseController
{
    public function __construct(
        protected PageQueryService $queryService,
        protected PageCommandService $commandService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $pages = $this->queryService->list(
                $request->only(['status', 'parent_id', 'root', 'search']),
                $request->integer('per_page', 15)
            );
            return $this->paginated(PageResource::collection($pages)->resource);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve pages: ' . $e->getMessage());
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $page = $this->queryService->find($id);
            if (!$page) return $this->notFound('Page not found');
            return $this->success(new PageResource($page));
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve page: ' . $e->getMessage());
        }
    }

    public function store(CreatePageRequest $request): JsonResponse
    {
        try {
            $page = $this->commandService->create($request->validated());
            return $this->created(new PageResource($page), 'Page created successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to create page: ' . $e->getMessage());
        }
    }

    public function update(UpdatePageRequest $request, string $id): JsonResponse
    {
        try {
            $page = $this->queryService->find($id);
            if (!$page) return $this->notFound('Page not found');
            $page = $this->commandService->update($page, $request->validated());
            return $this->success(new PageResource($page), 'Page updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update page: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $page = $this->queryService->find($id);
            if (!$page) return $this->notFound('Page not found');
            $this->commandService->delete($page);
            return $this->success(null, 'Page deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete page: ' . $e->getMessage());
        }
    }
}
