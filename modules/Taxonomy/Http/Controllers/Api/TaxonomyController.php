<?php

declare(strict_types=1);

namespace Modules\Taxonomy\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Taxonomy\Contracts\TaxonomyServiceContract;
use Modules\Taxonomy\Http\Requests\CreateTaxonomyRequest;
use Modules\Taxonomy\Http\Requests\ReorderTaxonomyRequest;
use Modules\Taxonomy\Http\Requests\UpdateTaxonomyRequest;
use Modules\Taxonomy\Http\Resources\TaxonomyResource;
use Modules\Taxonomy\Http\Resources\TaxonomyTypeResource;

class TaxonomyController extends BaseController
{
    public function __construct(
        protected TaxonomyServiceContract $taxonomyService
    ) {}

    public function types(): JsonResponse
    {
        $types = $this->taxonomyService->getTypes();

        return $this->success(TaxonomyTypeResource::collection($types));
    }

    public function index(Request $request, string $type): JsonResponse
    {
        $taxonomies = $this->taxonomyService->getTaxonomies(
            $type,
            $request->input('parent_id')
        );

        return $this->success(TaxonomyResource::collection($taxonomies));
    }

    public function tree(string $type): JsonResponse
    {
        $taxonomies = $this->taxonomyService->getTree($type);

        return $this->success(TaxonomyResource::collection($taxonomies));
    }

    public function show(string $id): JsonResponse
    {
        $taxonomy = $this->taxonomyService->find($id);

        if (!$taxonomy) {
            return $this->notFound('Taxonomy not found');
        }

        return $this->success(new TaxonomyResource($taxonomy));
    }

    public function showBySlug(string $type, string $slug): JsonResponse
    {
        $taxonomy = $this->taxonomyService->findBySlug($slug, $type);

        if (!$taxonomy) {
            return $this->notFound('Taxonomy not found');
        }

        return $this->success(new TaxonomyResource($taxonomy));
    }

    public function store(CreateTaxonomyRequest $request): JsonResponse
    {
        $taxonomy = $this->taxonomyService->create($request->validated());

        return $this->created(new TaxonomyResource($taxonomy));
    }

    public function update(UpdateTaxonomyRequest $request, string $id): JsonResponse
    {
        $taxonomy = $this->taxonomyService->find($id);

        if (!$taxonomy) {
            return $this->notFound('Taxonomy not found');
        }

        $taxonomy = $this->taxonomyService->update($taxonomy, $request->validated());

        return $this->success(new TaxonomyResource($taxonomy));
    }

    public function destroy(string $id): JsonResponse
    {
        $taxonomy = $this->taxonomyService->find($id);

        if (!$taxonomy) {
            return $this->notFound('Taxonomy not found');
        }

        $this->taxonomyService->delete($taxonomy);

        return $this->success(null, 'Taxonomy deleted');
    }

    public function reorder(ReorderTaxonomyRequest $request): JsonResponse
    {
        $this->taxonomyService->reorder($request->validated()['order']);

        return $this->success(null, 'Taxonomies reordered');
    }
}
