<?php

declare(strict_types=1);

namespace Modules\Media\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Media\Contracts\MediaServiceContract;
use Modules\Media\Http\Requests\UploadMediaRequest;
use Modules\Media\Http\Resources\MediaResource;

/**
 * Class MediaController
 * 
 * API controller for managing media files including uploads,
 * CRUD operations, folder management, and file processing.
 * 
 * @package Modules\Media\Http\Controllers\Api
 */
class MediaController extends BaseController
{
    /**
     * The media service instance for handling media-related business logic.
     *
     * @var MediaServiceContract
     */
    protected MediaServiceContract $mediaService;

    /**
     * Create a new MediaController instance.
     *
     * @param MediaServiceContract $mediaService The media service contract implementation
     */
    public function __construct(
        MediaServiceContract $mediaService
    ) {
        $this->mediaService = $mediaService;
    }

    /**
     * Display a paginated listing of media files.
     *
     * Supports filtering by folder, type, and search term.
     *
     * @param Request $request The incoming HTTP request containing filter parameters
     * @return JsonResponse Paginated list of media wrapped in MediaResource
     */
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

    /**
     * Display the specified media file by its UUID.
     *
     * @param string $id The UUID of the media to retrieve
     * @return JsonResponse The media data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $media = $this->mediaService->find($id);

        if (!$media) {
            return $this->notFound('Media not found');
        }

        return $this->success(new MediaResource($media));
    }

    /**
     * Upload a single media file.
     *
     * @param UploadMediaRequest $request The validated request containing the file
     * @return JsonResponse The uploaded media (HTTP 201)
     */
    public function store(UploadMediaRequest $request): JsonResponse
    {
        $file = $request->file('file');
        $options = $request->only(['folder_id', 'collection', 'alt_text', 'title']);
        $options['user_id'] = $request->user()->id;

        $media = $this->mediaService->upload($file, $options);

        return $this->created(new MediaResource($media), 'File uploaded successfully');
    }

    /**
     * Upload multiple media files at once.
     *
     * @param UploadMediaRequest $request The validated request containing files array
     * @return JsonResponse The uploaded media collection (HTTP 201)
     */
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

    /**
     * Update metadata for the specified media file.
     *
     * @param Request $request The request containing alt_text, title, or meta
     * @param string $id The UUID of the media to update
     * @return JsonResponse The updated media or 404 error
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $media = $this->mediaService->find($id);

        if (!$media) {
            return $this->notFound('Media not found');
        }

        $media = $this->mediaService->update($media, $request->only(['alt_text', 'title', 'meta']));

        return $this->success(new MediaResource($media), 'Media updated successfully');
    }

    /**
     * Delete the specified media file.
     *
     * @param string $id The UUID of the media to delete
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $media = $this->mediaService->find($id);

        if (!$media) {
            return $this->notFound('Media not found');
        }

        $this->mediaService->delete($media);

        return $this->success(null, 'Media deleted successfully');
    }

    /**
     * Move a media file to a different folder.
     *
     * @param Request $request The request containing 'folder_id'
     * @param string $id The UUID of the media to move
     * @return JsonResponse The moved media or 404 error
     */
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
