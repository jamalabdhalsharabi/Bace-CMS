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
use Modules\Taxonomy\Http\Requests\MergeTaxonomyRequest;
use Modules\Taxonomy\Http\Requests\ImportTaxonomyRequest;
use Modules\Taxonomy\Http\Requests\CreateTaxonomyTypeRequest;
use Modules\Taxonomy\Http\Requests\CreateTaxonomyTranslationRequest;
use Modules\Taxonomy\Http\Requests\MoveTaxonomyRequest;
use Modules\Taxonomy\Http\Requests\ChangeParentRequest;
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
        $types = $this->queryService->getAllTypes();

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
     */
    public function reorder(ReorderTaxonomyRequest $request): JsonResponse
    {
        $this->commandService->reorder($request->validated()['order']);
        return $this->success(null, 'Taxonomies reordered');
    }

    /** Create translated version. */
    public function createTranslation(CreateTaxonomyTranslationRequest $request, string $id): JsonResponse
    {
        $taxonomy = $this->queryService->find($id);
        if (!$taxonomy) return $this->notFound('Taxonomy not found');
        $translation = $this->commandService->createTranslation($taxonomy, $request->validated());
        return $this->created($translation);
    }

    /** Move taxonomy in tree. */
    public function move(MoveTaxonomyRequest $request, string $id): JsonResponse
    {
        $taxonomy = $this->queryService->find($id);
        if (!$taxonomy) return $this->notFound('Taxonomy not found');
        $taxonomy = $this->commandService->move($taxonomy, $request->parent_id, $request->integer('position', 0));
        return $this->success(new TaxonomyResource($taxonomy), 'Taxonomy moved');
    }

    /** Change parent of taxonomy. */
    public function changeParent(ChangeParentRequest $request, string $id): JsonResponse
    {
        $taxonomy = $this->queryService->find($id);
        if (!$taxonomy) return $this->notFound('Taxonomy not found');
        $taxonomy = $this->commandService->changeParent($taxonomy, $request->parent_id);
        return $this->success(new TaxonomyResource($taxonomy));
    }

    /** Merge two taxonomies. */
    public function merge(MergeTaxonomyRequest $request): JsonResponse
    {
        $result = $this->commandService->merge($request->source_id, $request->target_id);
        return $this->success($result, 'Taxonomies merged');
    }

    /** Activate taxonomy. */
    public function activate(string $id): JsonResponse
    {
        $taxonomy = $this->queryService->find($id);
        if (!$taxonomy) return $this->notFound('Taxonomy not found');
        $taxonomy = $this->commandService->activate($taxonomy);
        return $this->success(new TaxonomyResource($taxonomy), 'Taxonomy activated');
    }

    /** Deactivate taxonomy. */
    public function deactivate(string $id): JsonResponse
    {
        $taxonomy = $this->queryService->find($id);
        if (!$taxonomy) return $this->notFound('Taxonomy not found');
        $taxonomy = $this->commandService->deactivate($taxonomy);
        return $this->success(new TaxonomyResource($taxonomy), 'Taxonomy deactivated');
    }

    /** Import taxonomies. */
    public function import(ImportTaxonomyRequest $request): JsonResponse
    {
        $result = $this->commandService->import($request->type, $request->data, $request->input('mode', 'merge'));
        return $this->success($result, 'Taxonomies imported');
    }

    /** Export taxonomies. */
    public function export(Request $request, string $type): JsonResponse
    {
        $result = $this->queryService->export($type, $request->input('format', 'json'));
        return $this->success($result);
    }

    /** Create custom taxonomy type. */
    public function createType(CreateTaxonomyTypeRequest $request): JsonResponse
    {
        $type = $this->commandService->createType($request->validated());
        return $this->created(new TaxonomyTypeResource($type));
    }

    /** Update taxonomy type. */
    public function updateType(Request $request, string $typeId): JsonResponse
    {
        $type = $this->commandService->updateType($typeId, $request->all());
        return $this->success(new TaxonomyTypeResource($type));
    }

    /** Delete taxonomy type. */
    public function destroyType(string $typeId): JsonResponse
    {
        $this->commandService->deleteType($typeId);
        return $this->success(null, 'Taxonomy type deleted');
    }

    /** Get content statistics for taxonomy. */
    public function contentStats(string $id): JsonResponse
    {
        $taxonomy = $this->queryService->find($id);
        if (!$taxonomy) return $this->notFound('Taxonomy not found');
        $stats = $this->queryService->getContentStats($taxonomy);
        return $this->success($stats);
    }

    /** Clean empty taxonomies. */
    public function cleanEmpty(string $type): JsonResponse
    {
        $count = $this->commandService->cleanEmpty($type);
        return $this->success(['deleted' => $count], 'Empty taxonomies cleaned');
    }
}
