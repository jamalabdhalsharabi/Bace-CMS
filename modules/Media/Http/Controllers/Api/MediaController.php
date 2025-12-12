<?php

declare(strict_types=1);

namespace Modules\Media\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Media\Contracts\MediaServiceContract;
use Modules\Media\Http\Requests\UploadMediaRequest;
use Modules\Media\Http\Resources\MediaResource;

class MediaController extends BaseController
{
    public function __construct(
        protected MediaServiceContract $mediaService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $media = $this->mediaService->list(
            filters: $request->only(['folder_id', 'type', 'search']),
            perPage: $request->integer('per_page', 24)
        );

        return $this->paginated(
            MediaResource::collection($media)->resource
        );
    }

    public function show(string $id): JsonResponse
    {
        $media = $this->mediaService->find($id);

        if (!$media) {
            return $this->notFound('Media not found');
        }

        return $this->success(new MediaResource($media));
    }

    public function store(UploadMediaRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $options = $request->only(['folder_id', 'collection', 'alt_text', 'title']);
        $options['user_id'] = $request->user()->id;

        $media = $this->mediaService->upload($file, $options);

        return $this->created(new MediaResource($media), 'File uploaded successfully');
    }

    public function storeMultiple(UploadMediaRequest $request): JsonResponse
    {
        $files = $request->file('files');
        $options = $request->only(['folder_id', 'collection']);
        $options['user_id'] = $request->user()->id;

        $media = $this->mediaService->uploadMultiple($files, $options);

        return $this->created(
            MediaResource::collection($media),
            count($media) . ' files uploaded successfully'
        );
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $media = $this->mediaService->find($id);

        if (!$media) {
            return $this->notFound('Media not found');
        }

        $media = $this->mediaService->update($media, $request->only(['alt_text', 'title', 'meta']));

        return $this->success(new MediaResource($media), 'Media updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $media = $this->mediaService->find($id);

        if (!$media) {
            return $this->notFound('Media not found');
        }

        $this->mediaService->delete($media);

        return $this->success(null, 'Media deleted successfully');
    }

    public function move(Request $request, string $id): JsonResponse
    {
        $media = $this->mediaService->find($id);

        if (!$media) {
            return $this->notFound('Media not found');
        }

        $media = $this->mediaService->move($media, $request->input('folder_id'));

        return $this->success(new MediaResource($media), 'Media moved successfully');
    }
}
