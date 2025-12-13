<?php

declare(strict_types=1);

namespace Modules\Media\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Media\Application\Services\MediaCommandService;
use Modules\Media\Application\Services\MediaQueryService;
use Modules\Media\Http\Requests\UploadMediaRequest;
use Modules\Media\Http\Requests\UploadChunkRequest;
use Modules\Media\Http\Requests\CropImageRequest;
use Modules\Media\Http\Requests\RotateImageRequest;
use Modules\Media\Http\Requests\BulkIdsRequest;
use Modules\Media\Http\Requests\BulkMoveRequest;
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
        if (!$media) return $this->notFound('Media not found');
        $media = $this->commandService->move($id, $request->input('folder_id'));
        return $this->success(new MediaResource($media), 'Media moved successfully');
    }

    /** Upload large file in chunks. */
    public function uploadChunk(UploadChunkRequest $request): JsonResponse
    {
        $result = $this->commandService->uploadChunk(
            $request->file('chunk'),
            $request->upload_id,
            $request->chunk_index,
            $request->total_chunks,
            $request->filename,
            $request->only(['folder_id'])
        );
        
        if ($result['complete']) {
            return $this->created(new MediaResource($result['media']), 'Upload complete');
        }
        return $this->success(['progress' => $result['progress']], 'Chunk uploaded');
    }

    /** Replace existing file. */
    public function replace(Request $request, string $id): JsonResponse
    {
        $request->validate(['file' => 'required|file']);
        $media = $this->queryService->findById($id);
        if (!$media) return $this->notFound('Media not found');
        $media = $this->commandService->replace($id, $request->file('file'));
        return $this->success(new MediaResource($media), 'File replaced');
    }

    /** Duplicate/clone media. */
    public function duplicate(string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);
        if (!$media) return $this->notFound('Media not found');
        $clone = $this->commandService->duplicate($id);
        return $this->created(new MediaResource($clone), 'Media duplicated');
    }

    /** Optimize image. */
    public function optimize(string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);
        if (!$media) return $this->notFound('Media not found');
        $media = $this->commandService->optimize($id);
        return $this->success(new MediaResource($media), 'Image optimized');
    }

    /** Crop image. */
    public function crop(CropImageRequest $request, string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);
        if (!$media) return $this->notFound('Media not found');
        $media = $this->commandService->crop($id, $request->only(['x', 'y', 'width', 'height']));
        return $this->success(new MediaResource($media), 'Image cropped');
    }

    /** Rotate image. */
    public function rotate(RotateImageRequest $request, string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);
        if (!$media) return $this->notFound('Media not found');
        $media = $this->commandService->rotate($id, $request->degrees);
        return $this->success(new MediaResource($media), 'Image rotated');
    }

    /** Generate image variants. */
    public function generateVariants(Request $request, string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);
        if (!$media) return $this->notFound('Media not found');
        $variants = $this->commandService->generateVariants($id, $request->input('sizes', []));
        return $this->success(['variants' => $variants], 'Variants generated');
    }

    /** Regenerate all variants. */
    public function regenerateVariants(string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);
        if (!$media) return $this->notFound('Media not found');
        $this->commandService->regenerateVariants($id);
        return $this->success(null, 'Variants regenerated');
    }

    /** Search media. */
    public function search(Request $request): JsonResponse
    {
        $results = $this->queryService->search(
            $request->input('q'),
            $request->only(['type', 'folder_id', 'date_from', 'date_to']),
            $request->integer('per_page', 24)
        );
        return $this->paginated(MediaResource::collection($results)->resource);
    }

    /** Force delete permanently. */
    public function forceDestroy(string $id): JsonResponse
    {
        $media = $this->queryService->findByIdWithTrashed($id);
        if (!$media) return $this->notFound('Media not found');
        $this->commandService->forceDelete($id);
        return $this->success(null, 'Media permanently deleted');
    }

    /** Restore soft-deleted media. */
    public function restore(string $id): JsonResponse
    {
        $media = $this->commandService->restore($id);
        return $media ? $this->success(new MediaResource($media), 'Media restored') : $this->notFound('Media not found');
    }

    /** Generate temporary URL. */
    public function temporaryUrl(Request $request, string $id): JsonResponse
    {
        $request->validate(['expires_in' => 'nullable|integer|min:1|max:10080']);
        $media = $this->queryService->findById($id);
        if (!$media) return $this->notFound('Media not found');
        $url = $this->commandService->generateTemporaryUrl($id, $request->integer('expires_in', 60));
        return $this->success(['url' => $url, 'expires_at' => now()->addMinutes($request->integer('expires_in', 60))]);
    }

    /** Analyze media usage. */
    public function analyzeUsage(string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);
        if (!$media) return $this->notFound('Media not found');
        $usage = $this->queryService->analyzeUsage($id);
        return $this->success($usage);
    }

    /** Bulk delete media. */
    public function bulkDestroy(BulkIdsRequest $request): JsonResponse
    {
        $count = $this->commandService->bulkDelete($request->ids);
        return $this->success(['deleted' => $count], 'Media deleted');
    }

    /** Bulk move media. */
    public function bulkMove(BulkMoveRequest $request): JsonResponse
    {
        $count = $this->commandService->bulkMove($request->ids, $request->folder_id);
        return $this->success(['moved' => $count], 'Media moved');
    }

    /** Clean unused media. */
    public function cleanUnused(): JsonResponse
    {
        $count = $this->commandService->cleanUnused();
        return $this->success(['deleted' => $count], 'Unused media cleaned');
    }

    /** Remove duplicates. */
    public function removeDuplicates(): JsonResponse
    {
        $result = $this->commandService->removeDuplicates();
        return $this->success($result, 'Duplicates removed');
    }

    /** Extract metadata. */
    public function extractMetadata(string $id): JsonResponse
    {
        $media = $this->queryService->findById($id);
        if (!$media) return $this->notFound('Media not found');
        $metadata = $this->commandService->extractMetadata($id);
        return $this->success($metadata);
    }

    /** Get usage statistics. */
    public function stats(): JsonResponse
    {
        $stats = $this->queryService->getStats();
        return $this->success($stats);
    }
}
