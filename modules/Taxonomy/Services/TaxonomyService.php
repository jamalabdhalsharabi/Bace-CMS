<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Taxonomy\Contracts\TaxonomyServiceContract;
use Modules\Taxonomy\Domain\Models\Taxonomy;
use Modules\Taxonomy\Domain\Models\TaxonomyType;

/**
 * Class TaxonomyService
 *
 * Service class for managing taxonomies (categories, tags)
 * including CRUD operations, tree structure, and reordering.
 *
 * @package Modules\Taxonomy\Services
 */
class TaxonomyService implements TaxonomyServiceContract
{
    /**
     * Get all taxonomy types.
     *
     * @return Collection Collection of TaxonomyType models
     */
    public function getTypes(): Collection
    {
        return TaxonomyType::all();
    }

    /**
     * Get a taxonomy type by its slug.
     *
     * @param string $slug The type slug
     *
     * @return TaxonomyType|null The found type or null
     */
    public function getType(string $slug): ?TaxonomyType
    {
        return TaxonomyType::findBySlug($slug);
    }

    /**
     * Get taxonomies by type with optional parent filter.
     *
     * @param string $typeSlug The taxonomy type slug
     * @param string|null $parentId Parent taxonomy UUID or null for root
     *
     * @return Collection Collection of Taxonomy models
     */
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

    /**
     * Get taxonomy tree structure by type.
     *
     * @param string $typeSlug The taxonomy type slug
     *
     * @return Collection Root taxonomies with nested children
     */
    public function getTree(string $typeSlug): Collection
    {
        return Taxonomy::with(['children.children', 'translation', 'featuredImage'])
            ->ofType($typeSlug)
            ->root()
            ->active()
            ->ordered()
            ->get();
    }

    /**
     * Find a taxonomy by its UUID.
     *
     * @param string $id The taxonomy UUID
     *
     * @return Taxonomy|null The found taxonomy or null
     */
    public function find(string $id): ?Taxonomy
    {
        return Taxonomy::with(['type', 'translations', 'parent', 'children', 'featuredImage'])->find($id);
    }

    /**
     * Find a taxonomy by its slug.
     *
     * @param string $slug The taxonomy slug
     * @param string|null $typeSlug Optional type filter
     *
     * @return Taxonomy|null The found taxonomy or null
     */
    public function findBySlug(string $slug, ?string $typeSlug = null): ?Taxonomy
    {
        return Taxonomy::findBySlug($slug, $typeSlug)?->load(['type', 'translations', 'featuredImage']);
    }

    /**
     * Create a new taxonomy with translations.
     *
     * @param array $data Taxonomy data including type and translations
     *
     * @return Taxonomy The created taxonomy
     *
     * @throws \Throwable If transaction fails
     */
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

    /**
     * Update an existing taxonomy.
     *
     * @param Taxonomy $taxonomy The taxonomy to update
     * @param array $data Updated data
     *
     * @return Taxonomy The updated taxonomy
     *
     * @throws \Throwable If transaction fails
     */
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

    /**
     * Delete a taxonomy and reassign children.
     *
     * @param Taxonomy $taxonomy The taxonomy to delete
     *
     * @return bool True if successful
     */
    public function delete(Taxonomy $taxonomy): bool
    {
        Taxonomy::where('parent_id', $taxonomy->id)->update(['parent_id' => $taxonomy->parent_id]);

        return $taxonomy->delete();
    }

    /**
     * Reorder taxonomies by their IDs.
     *
     * @param array $order Array of taxonomy UUIDs in order
     *
     * @return void
     */
    public function reorder(array $order): void
    {
        foreach ($order as $index => $id) {
            Taxonomy::where('id', $id)->update(['ordering' => $index + 1]);
        }
    }
}
