<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Pricing\Contracts\PlanServiceContract;
use Modules\Pricing\Http\Requests\CreatePlanRequest;
use Modules\Pricing\Http\Requests\UpdatePlanRequest;
use Modules\Pricing\Http\Resources\PlanResource;

class PlanController extends BaseController
{
    public function __construct(protected PlanServiceContract $planService) {}

    public function index(Request $request): JsonResponse
    {
        $plans = $this->planService->list($request->only(['status', 'type']), $request->integer('per_page', 20));
        return $this->paginated(PlanResource::collection($plans)->resource);
    }

    public function active(): JsonResponse
    {
        return $this->success(PlanResource::collection($this->planService->getActive()));
    }

    public function show(string $id): JsonResponse
    {
        $plan = $this->planService->find($id);
        return $plan ? $this->success(new PlanResource($plan)) : $this->notFound('Plan not found');
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $plan = $this->planService->findBySlug($slug);
        return $plan ? $this->success(new PlanResource($plan)) : $this->notFound('Plan not found');
    }

    public function store(CreatePlanRequest $request): JsonResponse
    {
        return $this->created(new PlanResource($this->planService->create($request->validated())));
    }

    public function update(UpdatePlanRequest $request, string $id): JsonResponse
    {
        $plan = $this->planService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success(new PlanResource($this->planService->update($plan, $request->validated())));
    }

    public function destroy(string $id): JsonResponse
    {
        $plan = $this->planService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        try {
            $this->planService->delete($plan);
            return $this->success(null, 'Plan deleted');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function activate(string $id): JsonResponse
    {
        $plan = $this->planService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success(new PlanResource($this->planService->activate($plan)));
    }

    public function deactivate(string $id): JsonResponse
    {
        $plan = $this->planService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success(new PlanResource($this->planService->deactivate($plan)));
    }

    public function setDefault(string $id): JsonResponse
    {
        $plan = $this->planService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success(new PlanResource($this->planService->setAsDefault($plan)));
    }

    public function setRecommended(string $id): JsonResponse
    {
        $plan = $this->planService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success(new PlanResource($this->planService->setAsRecommended($plan)));
    }

    public function compare(Request $request): JsonResponse
    {
        $request->validate(['plans' => 'required|string']);
        $planSlugs = explode(',', $request->plans);
        $plans = \Modules\Pricing\Domain\Models\PricingPlan::whereIn('slug', $planSlugs)->pluck('id')->toArray();
        return $this->success($this->planService->compare($plans));
    }

    public function clone(Request $request, string $id): JsonResponse
    {
        $request->validate(['new_slug' => 'required|string|max:50|unique:pricing_plans,slug']);
        $plan = $this->planService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->created(new PlanResource($this->planService->clone($plan, $request->new_slug)));
    }

    public function reorder(Request $request): JsonResponse
    {
        $request->validate(['order' => 'required|array', 'order.*' => 'uuid']);
        $this->planService->reorder($request->order);
        return $this->success(null, 'Plans reordered');
    }

    public function analytics(string $id): JsonResponse
    {
        $plan = $this->planService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        return $this->success($this->planService->getAnalytics($plan));
    }

    public function export(Request $request): JsonResponse
    {
        return $this->success($this->planService->export($request->only(['status', 'format'])));
    }

    public function import(Request $request): JsonResponse
    {
        $request->validate([
            'data' => 'required|array',
            'mode' => 'nullable|in:merge,skip',
        ]);
        return $this->success($this->planService->import($request->data, $request->mode ?? 'merge'));
    }

    public function link(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'entity_type' => 'required|string|in:product,service,event,project',
            'entity_id' => 'required|uuid',
            'is_required' => 'nullable|boolean',
        ]);
        $plan = $this->planService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        
        $link = $this->planService->link($plan, $request->entity_type, $request->entity_id, $request->boolean('is_required'));
        return $this->created(['id' => $link->id, 'message' => 'Link created']);
    }

    public function unlink(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'entity_type' => 'required|string|in:product,service,event,project',
            'entity_id' => 'required|uuid',
        ]);
        $plan = $this->planService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        
        $deleted = $this->planService->unlink($plan, $request->entity_type, $request->entity_id);
        return $deleted ? $this->success(null, 'Link removed') : $this->notFound('Link not found');
    }

    public function links(string $id): JsonResponse
    {
        $plan = $this->planService->find($id);
        if (!$plan) return $this->notFound('Plan not found');
        
        return $this->success($this->planService->getLinks($plan));
    }
}
