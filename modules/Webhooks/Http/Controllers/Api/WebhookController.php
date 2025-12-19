<?php

declare(strict_types=1);

namespace Modules\Webhooks\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Webhooks\Application\Services\WebhookCommandService;
use Modules\Webhooks\Application\Services\WebhookQueryService;
use Modules\Webhooks\Http\Requests\StoreWebhookRequest;
use Modules\Webhooks\Http\Requests\UpdateWebhookRequest;
use Modules\Webhooks\Http\Resources\EmailLogResource;
use Modules\Webhooks\Http\Resources\WebhookResource;

/**
 * Webhook API Controller.
 *
 * Follows Clean Architecture principles:
 * - No validation logic (delegated to Form Requests)
 * - No business logic (delegated to Services)
 * - No direct Model usage (uses Repository Pattern via Services)
 */
class WebhookController extends BaseController
{
    public function __construct(
        protected WebhookQueryService $queryService,
        protected WebhookCommandService $commandService
    ) {
    }

    public function index(): JsonResponse
    {
        $webhooks = $this->queryService->getAll();
        return $this->success(WebhookResource::collection($webhooks));
    }

    public function store(StoreWebhookRequest $request): JsonResponse
    {
        $webhook = $this->commandService->create($request->validated());
        return $this->created(new WebhookResource($webhook));
    }

    public function show(string $id): JsonResponse
    {
        $webhook = $this->queryService->find($id);
        return $webhook ? $this->success(new WebhookResource($webhook)) : $this->notFound();
    }

    public function update(UpdateWebhookRequest $request, string $id): JsonResponse
    {
        $webhook = $this->queryService->find($id);
        if (!$webhook) return $this->notFound();

        $updated = $this->commandService->update($id, $request->validated());
        return $this->success(new WebhookResource($updated));
    }

    public function destroy(string $id): JsonResponse
    {
        $webhook = $this->queryService->find($id);
        if (!$webhook) return $this->notFound();

        $this->commandService->delete($id);
        return $this->success(null, 'Webhook deleted');
    }

    public function regenerateSecret(string $id): JsonResponse
    {
        $webhook = $this->queryService->find($id);
        if (!$webhook) return $this->notFound();

        $secret = $this->commandService->regenerateSecret($id);
        return $this->success(['secret' => $secret]);
    }

    public function emailLogs(Request $request): JsonResponse
    {
        $logs = $this->queryService->getEmailLogs($request->integer('per_page', 20));
        return $this->paginated(EmailLogResource::collection($logs)->resource);
    }
}
