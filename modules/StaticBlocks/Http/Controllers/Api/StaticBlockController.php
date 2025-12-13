<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Http\Controllers\BaseController;
use Modules\StaticBlocks\Domain\Models\StaticBlock;
use Modules\StaticBlocks\Http\Resources\StaticBlockResource;

/**
 * Class StaticBlockController
 *
 * API controller for managing static content blocks
 * including CRUD operations and translations.
 *
 * @package Modules\StaticBlocks\Http\Controllers\Api
 */
class StaticBlockController extends BaseController
{
    /**
     * Display a listing of active static blocks.
     *
     * @param Request $request The incoming request
     * @return JsonResponse Collection of active blocks
     */
    public function index(Request $request): JsonResponse
    {
        $blocks = StaticBlock::with('translation')->active()->get();
        return $this->success(StaticBlockResource::collection($blocks));
    }

    /**
     * Display the specified block by identifier.
     *
     * @param string $identifier The unique block identifier
     * @return JsonResponse The block or 404 error
     */
    public function show(string $identifier): JsonResponse
    {
        $block = StaticBlock::findByIdentifier($identifier);
        return $block ? $this->success(new StaticBlockResource($block)) : $this->notFound('Block not found');
    }

    /**
     * Store a newly created static block.
     *
     * @param Request $request The request with block data and translations
     * @return JsonResponse The created block (HTTP 201)
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'identifier' => 'required|string|max:100|unique:static_blocks,identifier',
            'type' => 'nullable|string|max:50',
            'translations' => 'required|array|min:1',
            'translations.*.title' => 'required|string|max:255',
            'translations.*.content' => 'required|string',
        ]);

        $block = DB::transaction(function () use ($data) {
            $b = StaticBlock::create([
                'identifier' => $data['identifier'],
                'type' => $data['type'] ?? 'html',
                'is_active' => true,
                'created_by' => auth()->id(),
            ]);
            foreach ($data['translations'] as $locale => $trans) {
                $b->translations()->create(['locale' => $locale, 'title' => $trans['title'], 'content' => $trans['content']]);
            }
            return $b->fresh(['translations']);
        });

        return $this->created(new StaticBlockResource($block));
    }

    /**
     * Update the specified static block.
     *
     * @param Request $request The request with updated data
     * @param string $id The UUID of the block
     * @return JsonResponse The updated block or 404 error
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');

        $block->update($request->only(['type', 'is_active', 'settings']));

        if ($request->has('translations')) {
            foreach ($request->translations as $locale => $trans) {
                $block->translations()->updateOrCreate(['locale' => $locale], $trans);
            }
        }

        StaticBlock::clearCache($block->identifier);
        return $this->success(new StaticBlockResource($block->fresh(['translations'])));
    }

    /**
     * Delete the specified static block.
     */
    public function destroy(string $id): JsonResponse
    {
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');
        StaticBlock::clearCache($block->identifier);
        $block->delete();
        return $this->success(null, 'Block deleted');
    }

    /**
     * Force delete block permanently.
     */
    public function forceDestroy(string $id): JsonResponse
    {
        $block = StaticBlock::withTrashed()->find($id);
        if (!$block) return $this->notFound('Block not found');
        StaticBlock::clearCache($block->identifier);
        $block->forceDelete();
        return $this->success(null, 'Block permanently deleted');
    }

    /**
     * Restore soft-deleted block.
     */
    public function restore(string $id): JsonResponse
    {
        $block = StaticBlock::withTrashed()->find($id);
        if (!$block) return $this->notFound('Block not found');
        $block->restore();
        return $this->success(new StaticBlockResource($block));
    }

    /**
     * Save block as draft.
     */
    public function saveDraft(Request $request, string $id): JsonResponse
    {
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');
        $block->update(['status' => 'draft', 'draft_content' => $request->content]);
        return $this->success(new StaticBlockResource($block));
    }

    /**
     * Publish the block.
     */
    public function publish(string $id): JsonResponse
    {
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');
        $block->update(['is_active' => true, 'published_at' => now()]);
        StaticBlock::clearCache($block->identifier);
        return $this->success(new StaticBlockResource($block));
    }

    /**
     * Unpublish the block.
     */
    public function unpublish(string $id): JsonResponse
    {
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');
        $block->update(['is_active' => false]);
        StaticBlock::clearCache($block->identifier);
        return $this->success(new StaticBlockResource($block));
    }

    /**
     * Archive the block.
     */
    public function archive(string $id): JsonResponse
    {
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');
        $block->update(['status' => 'archived']);
        StaticBlock::clearCache($block->identifier);
        return $this->success(new StaticBlockResource($block));
    }

    /**
     * Duplicate/clone the block.
     */
    public function duplicate(Request $request, string $id): JsonResponse
    {
        $request->validate(['new_identifier' => 'required|string|max:100|unique:static_blocks,identifier']);
        $block = StaticBlock::with('translations')->find($id);
        if (!$block) return $this->notFound('Block not found');

        $newBlock = DB::transaction(function () use ($block, $request) {
            $clone = $block->replicate();
            $clone->identifier = $request->new_identifier;
            $clone->is_active = false;
            $clone->save();
            
            foreach ($block->translations as $trans) {
                $clone->translations()->create($trans->only(['locale', 'title', 'content']));
            }
            return $clone;
        });

        return $this->created(new StaticBlockResource($newBlock));
    }

    /**
     * Embed block in page.
     */
    public function embedInPage(Request $request, string $id): JsonResponse
    {
        $request->validate(['page_id' => 'required|uuid', 'position' => 'nullable|string']);
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');
        
        DB::table('page_static_blocks')->insert([
            'page_id' => $request->page_id,
            'static_block_id' => $id,
            'position' => $request->position ?? 'content',
            'sort_order' => 0,
        ]);
        
        return $this->success(null, 'Block embedded in page');
    }

    /**
     * Remove block from page.
     */
    public function removeFromPage(Request $request, string $id): JsonResponse
    {
        $request->validate(['page_id' => 'required|uuid']);
        
        DB::table('page_static_blocks')
            ->where('page_id', $request->page_id)
            ->where('static_block_id', $id)
            ->delete();
        
        return $this->success(null, 'Block removed from page');
    }

    /**
     * Create translated version.
     */
    public function createTranslation(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'locale' => 'required|string|max:10',
            'title' => 'required|string|max:255',
            'content' => 'required|string',
        ]);
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');
        
        $translation = $block->translations()->create($request->validated());
        StaticBlock::clearCache($block->identifier);
        return $this->created($translation);
    }

    /**
     * Set visibility rules.
     */
    public function setVisibility(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'rules' => 'required|array',
            'rules.pages' => 'nullable|array',
            'rules.user_roles' => 'nullable|array',
            'rules.conditions' => 'nullable|array',
        ]);
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');
        
        $block->update(['visibility_rules' => $request->rules]);
        return $this->success(new StaticBlockResource($block));
    }

    /**
     * Schedule visibility.
     */
    public function scheduleVisibility(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'show_at' => 'nullable|date',
            'hide_at' => 'nullable|date|after:show_at',
        ]);
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');
        
        $block->update([
            'scheduled_show_at' => $request->show_at,
            'scheduled_hide_at' => $request->hide_at,
        ]);
        return $this->success(new StaticBlockResource($block));
    }

    /**
     * Preview block.
     */
    public function preview(string $id): JsonResponse
    {
        $block = StaticBlock::with('translations')->find($id);
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
        $block = StaticBlock::with('translations')->find($id);
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
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string|max:100|unique:static_blocks,identifier',
            'type' => 'nullable|string',
            'translations' => 'required|array',
        ]);
        
        $block = DB::transaction(function () use ($request) {
            $b = StaticBlock::create([
                'identifier' => $request->identifier,
                'type' => $request->type ?? 'html',
                'settings' => $request->settings ?? [],
                'is_active' => false,
                'created_by' => auth()->id(),
            ]);
            
            foreach ($request->translations as $locale => $trans) {
                $b->translations()->create([
                    'locale' => $locale,
                    'title' => $trans['title'],
                    'content' => $trans['content'],
                ]);
            }
            return $b;
        });
        
        return $this->created(new StaticBlockResource($block));
    }

    /**
     * Find usages of block.
     */
    public function findUsages(string $id): JsonResponse
    {
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');
        
        $usages = DB::table('page_static_blocks')
            ->join('pages', 'pages.id', '=', 'page_static_blocks.page_id')
            ->where('static_block_id', $id)
            ->select('pages.id', 'pages.slug', 'page_static_blocks.position')
            ->get();
        
        return $this->success(['usages' => $usages, 'count' => $usages->count()]);
    }
}
