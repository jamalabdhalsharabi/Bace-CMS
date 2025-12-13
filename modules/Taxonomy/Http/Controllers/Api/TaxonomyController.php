<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Taxonomy\Application\Services\TaxonomyCommandService;
use Modules\Taxonomy\Application\Services\TaxonomyQueryService;
use Modules\Taxonomy\Http\Requests\CreateTaxonomyRequest;
use Modules\Taxonomy\Http\Requests\ReorderTaxonomyRequest;
use Modules\Taxonomy\Http\Requests\UpdateTaxonomyRequest;
use Modules\Taxonomy\Http\Resources\TaxonomyResource;
use Modules\Taxonomy\Http\Resources\TaxonomyTypeResource;

class TaxonomyController extends BaseController
{
    public function __construct(
        protected TaxonomyQueryService $queryService,
        protected TaxonomyCommandService $commandService
    ) {
    }

    /**
     * Get all available taxonomy types.
     *
     * @return JsonResponse Collection of taxonomy types
     */
    public function types(): JsonResponse
    {
        $types = $this->queryService->getTypes();

        return $this->success(TaxonomyTypeResource::collection($types));
    }

    /**
     * Display taxonomies of a specific type.
     *
     * @param Request $request The request containing optional parent_id filter
     * @param string $type The taxonomy type (e.g., 'category', 'tag')
     * @return JsonResponse Collection of taxonomies
     */
    public function index(Request $request, string $type): JsonResponse
    {
        $taxonomies = $this->queryService->getTaxonomies(
            $type,
            $request->input('parent_id')
        );

        return $this->success(TaxonomyResource::collection($taxonomies));
    }

    /**
     * Get the hierarchical tree structure of taxonomies.
     *
     * @param string $type The taxonomy type
     * @return JsonResponse Taxonomies organized in tree structure
     */
    public function tree(string $type): JsonResponse
    {
        $taxonomies = $this->queryService->getTree($type);

        return $this->success(TaxonomyResource::collection($taxonomies));
    }

    /**
     * Display the specified taxonomy by its UUID.
     *
     * @param string $id The UUID of the taxonomy to retrieve
     * @return JsonResponse The taxonomy data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $taxonomy = $this->queryService->find($id);

        if (!$taxonomy) {
            return $this->notFound('Taxonomy not found');
        }

        return $this->success(new TaxonomyResource($taxonomy));
    }

    /**
     * Display the specified taxonomy by its type and slug.
     *
     * @param string $type The taxonomy type
     * @param string $slug The URL-friendly slug
     * @return JsonResponse The taxonomy data or 404 error
     */
    public function showBySlug(string $type, string $slug): JsonResponse
    {
        $taxonomy = $this->queryService->findBySlug($slug, $type);

        if (!$taxonomy) {
            return $this->notFound('Taxonomy not found');
        }

        return $this->success(new TaxonomyResource($taxonomy));
    }

    /**
     * Store a newly created taxonomy in the database.
     *
     * @param CreateTaxonomyRequest $request The validated request containing taxonomy data
     * @return JsonResponse The newly created taxonomy (HTTP 201)
     */
    public function store(CreateTaxonomyRequest $request): JsonResponse
    {
        $taxonomy = $this->queryService->create($request->validated());

        return $this->created(new TaxonomyResource($taxonomy));
    }

    /**
     * Update the specified taxonomy in the database.
     *
     * @param UpdateTaxonomyRequest $request The validated request containing updated data
     * @param string $id The UUID of the taxonomy to update
     * @return JsonResponse The updated taxonomy or 404 error
     */
    public function update(UpdateTaxonomyRequest $request, string $id): JsonResponse
    {
        $taxonomy = $this->queryService->find($id);

        if (!$taxonomy) {
            return $this->notFound('Taxonomy not found');
        }

        $taxonomy = $this->queryService->update($taxonomy, $request->validated());

        return $this->success(new TaxonomyResource($taxonomy));
    }

    /**
     * Delete the specified taxonomy.
     *
     * @param string $id The UUID of the taxonomy to delete
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $taxonomy = $this->queryService->find($id);

        if (!$taxonomy) {
            return $this->notFound('Taxonomy not found');
        }

        $this->queryService->delete($taxonomy);

        return $this->success(null, 'Taxonomy deleted');
    }

    /**
     * Reorder taxonomies based on the provided order array.
     *
     * @param ReorderTaxonomyRequest $request The validated request containing order array
     * @return JsonResponse Success message
     */
    public function reorder(ReorderTaxonomyRequest $request): JsonResponse
    {
        $this->queryService->reorder($request->validated()['order']);

        return $this->success(null, 'Taxonomies reordered');
    }
}
