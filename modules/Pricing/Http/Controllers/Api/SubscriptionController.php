<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Pricing\Contracts\SubscriptionServiceContract;
use Modules\Pricing\Http\Requests\CreateSubscriptionRequest;
use Modules\Pricing\Http\Resources\SubscriptionResource;

class SubscriptionController extends BaseController
{
    public function __construct(protected SubscriptionServiceContract $subscriptionService) {}

    public function index(): JsonResponse
    {
        $subscriptions = $this->subscriptionService->getForUser(auth()->id());
        return $this->success(SubscriptionResource::collection($subscriptions));
    }

    public function show(string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== auth()->id()) {
            return $this->notFound('Subscription not found');
        }
        return $this->success(new SubscriptionResource($subscription));
    }

    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        $subscription = $this->subscriptionService->create(
            auth()->id(),
            $request->plan_id,
            $request->validated()
        );
        return $this->created(new SubscriptionResource($subscription));
    }

    public function upgrade(Request $request, string $id): JsonResponse
    {
        $request->validate(['new_plan_id' => 'required|uuid|exists:pricing_plans,id']);
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== auth()->id()) {
            return $this->notFound('Subscription not found');
        }
        $subscription = $this->subscriptionService->upgrade($subscription, $request->new_plan_id);
        return $this->success(new SubscriptionResource($subscription), 'Subscription upgraded');
    }

    public function downgrade(Request $request, string $id): JsonResponse
    {
        $request->validate(['new_plan_id' => 'required|uuid|exists:pricing_plans,id']);
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== auth()->id()) {
            return $this->notFound('Subscription not found');
        }
        $subscription = $this->subscriptionService->downgrade($subscription, $request->new_plan_id);
        return $this->success(new SubscriptionResource($subscription), 'Downgrade scheduled');
    }

    public function cancel(Request $request, string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== auth()->id()) {
            return $this->notFound('Subscription not found');
        }
        $subscription = $this->subscriptionService->cancel($subscription, $request->reason);
        return $this->success(new SubscriptionResource($subscription), 'Subscription cancelled');
    }

    public function pause(string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== auth()->id()) {
            return $this->notFound('Subscription not found');
        }
        $subscription = $this->subscriptionService->pause($subscription);
        return $this->success(new SubscriptionResource($subscription), 'Subscription paused');
    }

    public function resume(string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== auth()->id()) {
            return $this->notFound('Subscription not found');
        }
        $subscription = $this->subscriptionService->resume($subscription);
        return $this->success(new SubscriptionResource($subscription), 'Subscription resumed');
    }

    public function refund(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'type' => 'required|in:full,prorated,partial',
            'amount' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string',
        ]);
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription) {
            return $this->notFound('Subscription not found');
        }
        $result = $this->subscriptionService->refund($subscription, $request->type, $request->amount);
        return $this->success($result, 'Refund processed');
    }

    public function extend(Request $request, string $id): JsonResponse
    {
        $request->validate([
            'days' => 'required|integer|min:1|max:365',
            'reason' => 'nullable|string',
            'notify_user' => 'nullable|boolean',
        ]);
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription) {
            return $this->notFound('Subscription not found');
        }
        $subscription = $this->subscriptionService->extend($subscription, $request->days, $request->reason);
        return $this->success(new SubscriptionResource($subscription), 'Subscription extended');
    }
}
