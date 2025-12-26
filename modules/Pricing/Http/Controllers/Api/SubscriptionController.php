<?php

declare(strict_types=1);

namespace Modules\Pricing\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Pricing\Contracts\SubscriptionServiceContract;
use Modules\Pricing\Http\Requests\ChangePlanRequest;
use Modules\Pricing\Http\Requests\CreateSubscriptionRequest;
use Modules\Pricing\Http\Requests\ExtendSubscriptionRequest;
use Modules\Pricing\Http\Requests\RefundSubscriptionRequest;
use Modules\Pricing\Http\Resources\SubscriptionResource;

/**
 * Class SubscriptionController
 *
 * API controller for managing user subscriptions including
 * CRUD, upgrades, downgrades, cancellation, and refunds.
 *
 * @package Modules\Pricing\Http\Controllers\Api
 */
class SubscriptionController extends BaseController
{
    /**
     * The subscription service instance.
     *
     * @var SubscriptionServiceContract
     */
    protected SubscriptionServiceContract $subscriptionService;

    /**
     * Create a new SubscriptionController instance.
     *
     * @param SubscriptionServiceContract $subscriptionService The subscription service
     */
    public function __construct(SubscriptionServiceContract $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    /**
     * Display subscriptions for the authenticated user.
     *
     * @return JsonResponse Collection of user subscriptions
     */
    public function index(): JsonResponse
    {
        $subscriptions = $this->subscriptionService->getForUser(request()->user()?->id);
        return $this->success(SubscriptionResource::collection($subscriptions));
    }

    /**
     * Display the specified subscription.
     *
     * @param string $id The UUID of the subscription
     * @return JsonResponse The subscription or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== request()->user()?->id) {
            return $this->notFound('Subscription not found');
        }
        return $this->success(new SubscriptionResource($subscription));
    }

    /**
     * Create a new subscription for the authenticated user.
     *
     * @param CreateSubscriptionRequest $request The validated subscription data
     * @return JsonResponse The created subscription (HTTP 201)
     */
    public function store(CreateSubscriptionRequest $request): JsonResponse
    {
        $subscription = $this->subscriptionService->create(
            request()->user()?->id,
            $request->plan_id,
            $request->validated()
        );
        return $this->created(new SubscriptionResource($subscription));
    }

    /**
     * Upgrade a subscription to a higher plan.
     *
     * @param Request $request The request containing new_plan_id
     * @param string $id The UUID of the subscription
     * @return JsonResponse The upgraded subscription or 404 error
     */
    public function upgrade(ChangePlanRequest $request, string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== request()->user()?->id) {
            return $this->notFound('Subscription not found');
        }
        $subscription = $this->subscriptionService->upgrade($subscription, $request->validated()['new_plan_id']);
        return $this->success(new SubscriptionResource($subscription), 'Subscription upgraded');
    }

    /**
     * Downgrade a subscription to a lower plan.
     *
     * @param Request $request The request containing new_plan_id
     * @param string $id The UUID of the subscription
     * @return JsonResponse The downgraded subscription or 404 error
     */
    public function downgrade(ChangePlanRequest $request, string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== request()->user()?->id) {
            return $this->notFound('Subscription not found');
        }
        $subscription = $this->subscriptionService->downgrade($subscription, $request->validated()['new_plan_id']);
        return $this->success(new SubscriptionResource($subscription), 'Downgrade scheduled');
    }

    /**
     * Cancel a subscription.
     *
     * @param Request $request The request with optional cancellation reason
     * @param string $id The UUID of the subscription
     * @return JsonResponse The cancelled subscription or 404 error
     */
    public function cancel(Request $request, string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== request()->user()?->id) {
            return $this->notFound('Subscription not found');
        }
        $subscription = $this->subscriptionService->cancel($subscription, $request->reason);
        return $this->success(new SubscriptionResource($subscription), 'Subscription cancelled');
    }

    /**
     * Pause a subscription temporarily.
     *
     * @param string $id The UUID of the subscription
     * @return JsonResponse The paused subscription or 404 error
     */
    public function pause(string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== request()->user()?->id) {
            return $this->notFound('Subscription not found');
        }
        $subscription = $this->subscriptionService->pause($subscription);
        return $this->success(new SubscriptionResource($subscription), 'Subscription paused');
    }

    /**
     * Resume a paused subscription.
     *
     * @param string $id The UUID of the subscription
     * @return JsonResponse The resumed subscription or 404 error
     */
    public function resume(string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription || $subscription->user_id !== request()->user()?->id) {
            return $this->notFound('Subscription not found');
        }
        $subscription = $this->subscriptionService->resume($subscription);
        return $this->success(new SubscriptionResource($subscription), 'Subscription resumed');
    }

    /**
     * Process a refund for a subscription.
     *
     * @param Request $request The request with refund type, amount, and reason
     * @param string $id The UUID of the subscription
     * @return JsonResponse Refund result or 404 error
     */
    public function refund(RefundSubscriptionRequest $request, string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription) {
            return $this->notFound('Subscription not found');
        }
        $data = $request->validated();
        $result = $this->subscriptionService->refund($subscription, $data['type'], $data['amount'] ?? null);
        return $this->success($result, 'Refund processed');
    }

    /**
     * Extend a subscription by a specified number of days.
     *
     * @param Request $request The request with days, reason, and notify_user flag
     * @param string $id The UUID of the subscription
     * @return JsonResponse The extended subscription or 404 error
     */
    public function extend(ExtendSubscriptionRequest $request, string $id): JsonResponse
    {
        $subscription = $this->subscriptionService->find($id);
        if (!$subscription) {
            return $this->notFound('Subscription not found');
        }
        $data = $request->validated();
        $subscription = $this->subscriptionService->extend($subscription, $data['days'], $data['reason'] ?? null);
        return $this->success(new SubscriptionResource($subscription), 'Subscription extended');
    }
}
