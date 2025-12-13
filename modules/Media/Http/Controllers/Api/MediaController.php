<?php

declare(strict_types=1);

namespace Modules\Media\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Media\Application\Services\MediaCommandService;
use Modules\Media\Application\Services\MediaQueryService;
use Modules\Media\Http\Requests\UploadMediaRequest;
use Modules\Media\Http\Resources\MediaResource;

class MediaController extends BaseController
{
    public function __construct(
        protected MediaQueryService $queryService,
        protected MediaCommandService $commandService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $media = $this->queryService->paginate(
            $request->integer('per_page', 24),
            $request->only(['folder_id', 'type', 'search'])
        );

        return $this->paginated(MediaResource::collection($media)->resource);
    }

    public function show(string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);

        if (!$media) {
            return $this->notFound('Media not found');
        }

        return $this->success(new MediaResource($media));
    }

    public function store(UploadMediaRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $options = $request->only(['folder_id', 'collection', 'alt_text', 'title']);
        $options['uploaded_by'] = $request->user()->id;

        $media = $this->commandService->upload($file, $options);

        return $this->created(new MediaResource($media), 'File uploaded successfully');
    }

    public function storeMultiple(UploadMediaRequest $request): JsonResponse
    {
        $files = $request->file('files');
        $options = $request->only(['folder_id', 'collection']);
        $options['uploaded_by'] = $request->user()->id;

        $mediaItems = [];
        foreach ($files as $file) {
            $mediaItems[] = $this->commandService->upload($file, $options);
        }

        return $this->created(
            MediaResource::collection($mediaItems),
            count($mediaItems) . ' files uploaded successfully'
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);

        if (!$media) {
            return $this->notFound('Media not found');
        }

        $media = $this->commandService->update($id, $request->only(['alt_text', 'title', 'meta']));

        return $this->success(new MediaResource($media), 'Media updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);

        if (!$media) {
            return $this->notFound('Media not found');
        }

        $this->commandService->delete($id);

        return $this->success(null, 'Media deleted successfully');
    }

    public function move(Request $request, string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);

        if (!$media) {
            return $this->notFound('Media not found');
        }

        $media = $this->commandService->move($id, $request->input('folder_id'));

        return $this->success(new MediaResource($media), 'Media moved successfully');
    }
}
