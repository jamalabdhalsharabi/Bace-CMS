<?php

declare(strict_types=1);

namespace Modules\Media\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Media\Application\Services\MediaCommandService;
use Modules\Media\Application\Services\MediaQueryService;

class FolderController extends BaseController
{
    public function __construct(
        protected MediaQueryService $queryService,
        protected MediaCommandService $commandService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $folders = $this->queryService->getFolders($request->input('parent_id'));

        return $this->success($folders);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'parent_id' => 'nullable|uuid|exists:media_folders,id',
        ]);

        $folder = $this->commandService->createFolder($request->only(['name', 'parent_id']));

        return $this->created($folder, 'Folder created successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $this->commandService->deleteFolder($id);

        return $this->success(null, 'Folder deleted successfully');
    }
}
