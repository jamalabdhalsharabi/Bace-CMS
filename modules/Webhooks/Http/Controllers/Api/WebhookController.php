<?php

declare(strict_types=1);

namespace Modules\Webhooks\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Webhooks\Domain\Models\EmailLog;
use Modules\Webhooks\Domain\Models\Webhook;
use Modules\Webhooks\Http\Resources\EmailLogResource;
use Modules\Webhooks\Http\Resources\WebhookResource;

/**
 * Class WebhookController
 *
 * API controller for managing webhooks including
 * CRUD operations, secret regeneration, and email logs.
 *
 * @package Modules\Webhooks\Http\Controllers\Api
 */
class WebhookController extends BaseController
{
    /**
     * Display a listing of all webhooks.
     *
     * @return JsonResponse Collection of webhooks with logs
     */
    public function index(): JsonResponse
    {
        return $this->success(WebhookResource::collection(Webhook::with('logs')->get()));
    }

    /**
     * Store a newly created webhook.
     *
     * @param Request $request The request with webhook configuration
     * @return JsonResponse The created webhook (HTTP 201)
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'url' => 'required|url|max:500',
            'events' => 'required|array|min:1',
            'events.*' => 'string',
            'headers' => 'nullable|array',
        ]);

        $webhook = Webhook::create([
            'name' => $data['name'],
            'url' => $data['url'],
            'secret' => Str::random(32),
            'events' => $data['events'],
            'headers' => $data['headers'] ?? null,
            'is_active' => true,
            'created_by' => auth()->id(),
        ]);

        return $this->created(new WebhookResource($webhook));
    }

    /**
     * Display the specified webhook.
     *
     * @param string $id The UUID of the webhook
     * @return JsonResponse The webhook with logs or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $webhook = Webhook::with('logs')->find($id);
        return $webhook ? $this->success(new WebhookResource($webhook)) : $this->notFound();
    }

    /**
     * Update the specified webhook.
     *
     * @param Request $request The request with updated data
     * @param string $id The UUID of the webhook
     * @return JsonResponse The updated webhook or 404 error
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $webhook = Webhook::find($id);
        if (!$webhook) return $this->notFound();

        $webhook->update($request->only(['name', 'url', 'events', 'headers', 'is_active']));
        return $this->success(new WebhookResource($webhook->fresh()));
    }

    /**
     * Delete the specified webhook and its logs.
     *
     * @param string $id The UUID of the webhook
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $webhook = Webhook::find($id);
        if (!$webhook) return $this->notFound();
        $webhook->logs()->delete();
        $webhook->delete();
        return $this->success(null, 'Webhook deleted');
    }

    /**
     * Regenerate the webhook secret key.
     *
     * @param string $id The UUID of the webhook
     * @return JsonResponse The new secret or 404 error
     */
    public function regenerateSecret(string $id): JsonResponse
    {
        $webhook = Webhook::find($id);
        if (!$webhook) return $this->notFound();
        $webhook->update(['secret' => Str::random(32)]);
        return $this->success(['secret' => $webhook->secret]);
    }

    /**
     * Display paginated email logs.
     *
     * @param Request $request The request with pagination options
     * @return JsonResponse Paginated list of email logs
     */
    public function emailLogs(Request $request): JsonResponse
    {
        $logs = EmailLog::latest('created_at')->paginate($request->integer('per_page', 20));
        return $this->paginated(EmailLogResource::collection($logs)->resource);
    }
}
