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

class WebhookController extends BaseController
{
    public function index(): JsonResponse
    {
        return $this->success(WebhookResource::collection(Webhook::with('logs')->get()));
    }

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

    public function show(string $id): JsonResponse
    {
        $webhook = Webhook::with('logs')->find($id);
        return $webhook ? $this->success(new WebhookResource($webhook)) : $this->notFound();
    }

    public function update(Request $request, string $id): JsonResponse
    {
        $webhook = Webhook::find($id);
        if (!$webhook) return $this->notFound();

        $webhook->update($request->only(['name', 'url', 'events', 'headers', 'is_active']));
        return $this->success(new WebhookResource($webhook->fresh()));
    }

    public function destroy(string $id): JsonResponse
    {
        $webhook = Webhook::find($id);
        if (!$webhook) return $this->notFound();
        $webhook->logs()->delete();
        $webhook->delete();
        return $this->success(null, 'Webhook deleted');
    }

    public function regenerateSecret(string $id): JsonResponse
    {
        $webhook = Webhook::find($id);
        if (!$webhook) return $this->notFound();
        $webhook->update(['secret' => Str::random(32)]);
        return $this->success(['secret' => $webhook->secret]);
    }

    public function emailLogs(Request $request): JsonResponse
    {
        $logs = EmailLog::latest('created_at')->paginate($request->integer('per_page', 20));
        return $this->paginated(EmailLogResource::collection($logs)->resource);
    }
}
