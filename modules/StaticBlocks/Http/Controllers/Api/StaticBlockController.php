<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\StaticBlocks\Application\Services\StaticBlockCommandService;
use Modules\StaticBlocks\Application\Services\StaticBlockQueryService;
use Modules\StaticBlocks\Http\Requests\CreateTranslationRequest;
use Modules\StaticBlocks\Http\Requests\DuplicateStaticBlockRequest;
use Modules\StaticBlocks\Http\Requests\EmbedInPageRequest;
use Modules\StaticBlocks\Http\Requests\ImportStaticBlockRequest;
use Modules\StaticBlocks\Http\Requests\RemoveFromPageRequest;
use Modules\StaticBlocks\Http\Requests\ScheduleVisibilityRequest;
use Modules\StaticBlocks\Http\Requests\SetVisibilityRequest;
use Modules\StaticBlocks\Http\Requests\StoreStaticBlockRequest;
use Modules\StaticBlocks\Http\Requests\UpdateStaticBlockRequest;
use Modules\StaticBlocks\Http\Resources\StaticBlockResource;

/**
 * Static Block API Controller.
 *
 * Follows Clean Architecture principles:
 * - No validation logic (delegated to Form Requests)
 * - No business logic (delegated to Services)
 * - No direct Model usage (uses Repository Pattern via Services)
 */
class StaticBlockController extends BaseController
{
    public function __construct(
        protected StaticBlockQueryService $queryService,
        protected StaticBlockCommandService $commandService
    ) {
    }

    /**
     * Display a listing of active static blocks.
     */
    public function index(Request $request): JsonResponse
    {
        $blocks = $this->queryService->getActive();
        return $this->success(StaticBlockResource::collection($blocks));
    }

    /**
     * Display the specified block by identifier.
     */
    public function show(string $identifier): JsonResponse
    {
        $block = $this->queryService->findByIdentifier($identifier);
        return $block ? $this->success(new StaticBlockResource($block)) : $this->notFound('Block not found');
    }

    /**
     * Store a newly created static block.
     */
    public function store(StoreStaticBlockRequest $request): JsonResponse
    {
        $block = $this->commandService->create($request->validated());
        return $this->created(new StaticBlockResource($block));
    }

    /**
     * Update the specified static block.
     */
    public function update(UpdateStaticBlockRequest $request, string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');

        $updated = $this->commandService->update($block, $request->validated());
        return $this->success(new StaticBlockResource($updated));
    }

    /**
     * Delete the specified static block.
     */
    public function destroy(string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        $this->commandService->delete($block);
        return $this->success(null, 'Block deleted');
    }

    /**
     * Force delete block permanently.
     */
    public function forceDestroy(string $id): JsonResponse
    {
        $deleted = $this->commandService->forceDelete($id);
        return $deleted ? $this->success(null, 'Block permanently deleted') : $this->notFound('Block not found');
    }

    /**
     * Restore soft-deleted block.
     */
    public function restore(string $id): JsonResponse
    {
        $block = $this->commandService->restore($id);
        return $block ? $this->success(new StaticBlockResource($block)) : $this->notFound('Block not found');
    }

    /**
     * Save block as draft.
     */
    public function saveDraft(Request $request, string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        $saved = $this->commandService->saveDraft($block, $request->input('content'));
        return $this->success(new StaticBlockResource($saved));
    }

    /**
     * Publish the block.
     */
    public function publish(string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        $published = $this->commandService->publish($block);
        return $this->success(new StaticBlockResource($published));
    }

    /**
     * Unpublish the block.
     */
    public function unpublish(string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        $unpublished = $this->commandService->unpublish($block);
        return $this->success(new StaticBlockResource($unpublished));
    }

    /**
     * Archive the block.
     */
    public function archive(string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        $archived = $this->commandService->archive($block);
        return $this->success(new StaticBlockResource($archived));
    }

    /**
     * Duplicate/clone the block.
     */
    public function duplicate(DuplicateStaticBlockRequest $request, string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');

        $newBlock = $this->commandService->duplicate($id, $request->validated()['new_identifier']);
        return $this->created(new StaticBlockResource($newBlock));
    }

    /**
     * Embed block in page.
     */
    public function embedInPage(EmbedInPageRequest $request, string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        
        $this->commandService->embedInPage($id, $request->validated());
        return $this->success(null, 'Block embedded in page');
    }

    /**
     * Remove block from page.
     */
    public function removeFromPage(RemoveFromPageRequest $request, string $id): JsonResponse
    {
        $this->commandService->removeFromPage($id, $request->validated()['page_id']);
        return $this->success(null, 'Block removed from page');
    }

    /**
     * Create translated version.
     */
    public function createTranslation(CreateTranslationRequest $request, string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        
        $translation = $this->commandService->createTranslation($block, $request->validated());
        return $this->created($translation);
    }

    /**
     * Set visibility rules.
     */
    public function setVisibility(SetVisibilityRequest $request, string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        
        $updated = $this->commandService->setVisibility($block, $request->validated()['rules']);
        return $this->success(new StaticBlockResource($updated));
    }

    /**
     * Schedule visibility.
     */
    public function scheduleVisibility(ScheduleVisibilityRequest $request, string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        
        $updated = $this->commandService->scheduleVisibility($block, $request->validated());
        return $this->success(new StaticBlockResource($updated));
    }

    /**
     * Preview block.
     */
    public function preview(string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        
        return $this->success([
            'html' => $block->renderPreview(),
            'block' => new StaticBlockResource($block),
        ]);
    }

    /**
     * Export block.
     */
    public function export(string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        
        return $this->success([
            'identifier' => $block->identifier,
            'type' => $block->type,
            'settings' => $block->settings,
            'translations' => $block->translations->keyBy('locale'),
        ]);
    }

    /**
     * Import block.
     */
    public function import(ImportStaticBlockRequest $request): JsonResponse
    {
        $block = $this->commandService->import($request->validated());
        return $this->created(new StaticBlockResource($block));
    }

    /**
     * Find usages of block.
     */
    public function findUsages(string $id): JsonResponse
    {
        $block = $this->queryService->find($id);
        if (!$block) return $this->notFound('Block not found');
        
        $usages = $this->queryService->findUsages($id);
        return $this->success(['usages' => $usages, 'count' => $usages->count()]);
    }
}
