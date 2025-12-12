<?php

declare(strict_types=1);

namespace Modules\StaticBlocks\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Core\Http\Controllers\BaseController;
use Modules\StaticBlocks\Domain\Models\StaticBlock;
use Modules\StaticBlocks\Http\Resources\StaticBlockResource;

class StaticBlockController extends BaseController
{
    public function index(Request $request): JsonResponse
    {
        $blocks = StaticBlock::with('translation')->active()->get();
        return $this->success(StaticBlockResource::collection($blocks));
    }

    public function show(string $identifier): JsonResponse
    {
        $block = StaticBlock::findByIdentifier($identifier);
        return $block ? $this->success(new StaticBlockResource($block)) : $this->notFound('Block not found');
    }

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

    public function destroy(string $id): JsonResponse
    {
        $block = StaticBlock::find($id);
        if (!$block) return $this->notFound('Block not found');
        StaticBlock::clearCache($block->identifier);
        $block->delete();
        return $this->success(null, 'Block deleted');
    }
}
