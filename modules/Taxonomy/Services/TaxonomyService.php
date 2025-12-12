<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Taxonomy\Contracts\TaxonomyServiceContract;
use Modules\Taxonomy\Domain\Models\Taxonomy;
use Modules\Taxonomy\Domain\Models\TaxonomyType;

class TaxonomyService implements TaxonomyServiceContract
{
    public function getTypes(): Collection
    {
        return TaxonomyType::all();
    }

    public function getType(string $slug): ?TaxonomyType
    {
        return TaxonomyType::findBySlug($slug);
    }

    public function getTaxonomies(string $typeSlug, ?string $parentId = null): Collection
    {
        $query = Taxonomy::with(['translation', 'featuredImage'])
            ->ofType($typeSlug)
            ->active()
            ->ordered();

        if ($parentId === null) {
            $query->root();
        } else {
            $query->where('parent_id', $parentId);
        }

        return $query->get();
    }

    public function getTree(string $typeSlug): Collection
    {
        return Taxonomy::with(['children.children', 'translation', 'featuredImage'])
            ->ofType($typeSlug)
            ->root()
            ->active()
            ->ordered()
            ->get();
    }

    public function find(string $id): ?Taxonomy
    {
        return Taxonomy::with(['type', 'translations', 'parent', 'children', 'featuredImage'])->find($id);
    }

    public function findBySlug(string $slug, ?string $typeSlug = null): ?Taxonomy
    {
        return Taxonomy::findBySlug($slug, $typeSlug)?->load(['type', 'translations', 'featuredImage']);
    }

    public function create(array $data): Taxonomy
    {
        return DB::transaction(function () use ($data) {
            $type = TaxonomyType::findBySlug($data['type']);

            $taxonomy = Taxonomy::create([
                'type_id' => $type->id,
                'parent_id' => $data['parent_id'] ?? null,
                'featured_image_id' => $data['featured_image_id'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $taxonomy->translations()->create([
                        'locale' => $locale,
                        'name' => $trans['name'],
                        'slug' => $trans['slug'] ?? Str::slug($trans['name']),
                        'description' => $trans['description'] ?? null,
                        'meta_title' => $trans['meta_title'] ?? null,
                        'meta_description' => $trans['meta_description'] ?? null,
                    ]);
                }
            }

            return $taxonomy->fresh(['type', 'translations', 'featuredImage']);
        });
    }

    public function update(Taxonomy $taxonomy, array $data): Taxonomy
    {
        return DB::transaction(function () use ($taxonomy, $data) {
            $taxonomy->update([
                'parent_id' => $data['parent_id'] ?? $taxonomy->parent_id,
                'featured_image_id' => $data['featured_image_id'] ?? $taxonomy->featured_image_id,
                'is_active' => $data['is_active'] ?? $taxonomy->is_active,
            ]);

            if (!empty($data['translations'])) {
                foreach ($data['translations'] as $locale => $trans) {
                    $taxonomy->translations()->updateOrCreate(
                        ['locale' => $locale],
                        [
                            'name' => $trans['name'],
                            'slug' => $trans['slug'] ?? Str::slug($trans['name']),
                            'description' => $trans['description'] ?? null,
                            'meta_title' => $trans['meta_title'] ?? null,
                            'meta_description' => $trans['meta_description'] ?? null,
                        ]
                    );
                }
            }

            return $taxonomy->fresh(['type', 'translations', 'featuredImage']);
        });
    }

    public function delete(Taxonomy $taxonomy): bool
    {
        Taxonomy::where('parent_id', $taxonomy->id)->update(['parent_id' => $taxonomy->parent_id]);

        return $taxonomy->delete();
    }

    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            Taxonomy::where('id', $id)->update(['ordering' => $index + 1]);
        }
    }
}
