<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Pricing\Application\Services\PlanCommandService;
use Modules\Pricing\Application\Services\PlanQueryService;
use Modules\Pricing\Http\Requests\CreatePlanRequest;
use Modules\Pricing\Http\Requests\UpdatePlanRequest;
use Modules\Pricing\Http\Resources\PlanResource;

class PlanController extends BaseController
{
    public function __construct(
        protected PlanQueryService $queryService,
        protected PlanCommandService $commandService
    ) {
    }

    /**
     * Display a paginated listing of pricing plans.
     *
     * @param Request $request The request containing optional filters
     * @return JsonResponse Paginated list of plans
     */
    public function index(Request $request): JsonResponse
    {
        $plans = $this->queryService->list($request->only(['status', 'type']), $request->integer('per_page', 20));
        return $this->paginated(PlanResource::collection($plans)->resource);
    }

    /**
     * Display only active pricing plans.
     *
     * @return JsonResponse Collection of active plans
     */
    public function active(): JsonResponse
    {
        return $this->success(PlanResource::collection($this->queryService->getActive()));
    }

    /**
     * Display the specified plan by its UUID.
     *
     * @param string $id The UUID of the plan to retrieve
     * @return JsonResponse The plan data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $plan = $this->queryService->find($id);
        return $plan ? $this->success(new PlanResource($plan)) : $this->notFound('Plan not found');
    }

    /**
     * Display the specified plan by its slug.
     *
     * @param string $slug The URL-friendly slug of the plan
     * @return JsonResponse The plan data or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $plan = $this->queryService->findBySlug($slug);
        return $plan ? $this->success(new PlanResource($plan)) : $this->notFound('Plan not found');
    }

    /**
     * Store a newly created pricing plan.
     *
     * @param CreatePlanRequest $request The validated request containing plan data
     * @return JsonResponse The newly created plan (HTTP 201)
     */
    public function store(CreatePlanRequest $request): JsonResponse
    {
        return $this->created(new PlanResource($this->queryService->create($request->validated())));
    }

    /**
     * Update the specified pricing plan.
     *
     * @param UpdatePlanRequest $request The validated request containing updated data
     * @param string $id The UUID of the plan to update
     * @return JsonResponse The updated plan or 404 error
     */
    public function update(UpdatePlanRequest $request, string $id): JsonResponse
    {
        $plan = $this->queryService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success(new PlanResource($this->queryService->update($plan, $request->validated())));
    }

    /**
     * Delete the specified pricing plan.
     *
     * @param string $id The UUID of the plan to delete
     * @return JsonResponse Success message or error
     * @throws \Exception If plan has active subscriptions
     */
    public function destroy(string $id): JsonResponse
    {
        $plan = $this->queryService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        try {
            $this->queryService->delete($plan);
            return $this->success(null, 'Plan deleted');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Activate a pricing plan.
     *
     * @param string $id The UUID of the plan to activate
     * @return JsonResponse The activated plan or 404 error
     */
    public function activate(string $id): JsonResponse
    {
        $plan = $this->queryService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success(new PlanResource($this->queryService->activate($plan)));
    }

    /**
     * Deactivate a pricing plan.
     *
     * @param string $id The UUID of the plan to deactivate
     * @return JsonResponse The deactivated plan or 404 error
     */
    public function deactivate(string $id): JsonResponse
    {
        $plan = $this->queryService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success(new PlanResource($this->queryService->deactivate($plan)));
    }

    /**
     * Set a plan as the default option.
     *
     * @param string $id The UUID of the plan
     * @return JsonResponse The updated plan or 404 error
     */
    public function setDefault(string $id): JsonResponse
    {
        $plan = $this->queryService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success(new PlanResource($this->queryService->setAsDefault($plan)));
    }

    /**
     * Set a plan as the recommended option.
     *
     * @param string $id The UUID of the plan
     * @return JsonResponse The updated plan or 404 error
     */
    public function setRecommended(string $id): JsonResponse
    {
        $plan = $this->queryService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success(new PlanResource($this->queryService->setAsRecommended($plan)));
    }

    /**
     * Compare multiple pricing plans.
     *
     * @param Request $request The request containing comma-separated plan slugs
     * @return JsonResponse Comparison data for the specified plans
     */
    public function compare(Request $request): JsonResponse
    {
        $request->validate(['plans' => 'required|string']);
        $planSlugs = explode(',', $request->plans);
        $plans = \Modules\Pricing\Domain\Models\PricingPlan::whereIn('slug', $planSlugs)->pluck('id')->toArray();
        return $this->success($this->queryService->compare($plans));
    }

    /**
     * Clone an existing pricing plan with a new slug.
     *
     * @param Request $request The request containing new_slug
     * @param string $id The UUID of the plan to clone
     * @return JsonResponse The cloned plan (HTTP 201) or 404 error
     */
    public function clone(Request $request, string $id): JsonResponse
    {
        $request->validate(['new_slug' => 'required|string|max:50|unique:pricing_plans,slug']);
        $plan = $this->queryService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->created(new PlanResource($this->queryService->clone($plan, $request->new_slug)));
    }

    /**
     * Reorder pricing plans.
     *
     * @param Request $request The request containing order array of UUIDs
     * @return JsonResponse Success message
     */
    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'uuid']);
        $this->queryService->reorder($request->order);
        return $this->success(null, 'Plans reordered');
    }

    /**
     * Get analytics data for a pricing plan.
     *
     * @param string $id The UUID of the plan
     * @return JsonResponse Analytics data or 404 error
     */
    public function analytics(string $id): JsonResponse
    {
        $plan = $this->queryService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success($this->queryService->getAnalytics($plan));
    }

    /**
     * Export pricing plans data.
     *
     * @param Request $request The request containing optional status and format filters
     * @return JsonResponse Exported plan data
     */
    public function export(Request $request): JsonResponse
    {
        return $this->success($this->queryService->export($request->only(['status', 'format'])));
    }

    /**
     * Import pricing plans from data array.
     *
     * @param Request $request The request containing data array and optional mode
     * @return JsonResponse Import result
     */
    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|array',
            'mode' => 'nullable|in:merge,skip',
        ]);
        return $this->success($this->queryService->import($request->data, $request->mode ?? 'merge'));
    }

    /**
     * Link a pricing plan to an entity (product, service, event, project).
     *
     * @param Request $request The request containing entity_type, entity_id, and is_required
     * @param string $id The UUID of the plan
     * @return JsonResponse The created link (HTTP 201) or 404 error
     */
    public function link(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'entity_type' => 'required|string|in:product,service,event,project',
            'entity_id' => 'required|uuid',
            'is_required' => 'nullable|boolean',
        ]);
        $plan = $this->queryService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        
        $link = $this->queryService->link($plan, $request->entity_type, $request->entity_id, $request->boolean('is_required'));
        return $this->created(['id' => $link->id, 'message' => 'Link created']);
    }

    /**
     * Remove a link between a plan and an entity.
     *
     * @param Request $request The request containing entity_type and entity_id
     * @param string $id The UUID of the plan
     * @return JsonResponse Success message or 404 error
     */
    public function unlink(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'entity_type' => 'required|string|in:product,service,event,project',
            'entity_id' => 'required|uuid',
        ]);
        $plan = $this->queryService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        
        $deleted = $this->queryService->unlink($plan, $request->entity_type, $request->entity_id);
        return $deleted ? $this->success(null, 'Link removed') : $this->notFound('Link not found');
    }

    /**
     * Get all entity links for a pricing plan.
     *
     * @param string $id The UUID of the plan
     * @return JsonResponse Array of linked entities or 404 error
     */
    public function links(string $id): JsonResponse
    {
        $plan = $this->queryService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        
        return $this->success($this->queryService->getLinks($plan));
    }
}
