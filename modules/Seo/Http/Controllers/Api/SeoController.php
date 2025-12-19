<?php

declare(strict_types=1);

namespace Modules\Seo\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Seo\Application\Services\SeoCommandService;
use Modules\Seo\Application\Services\SeoQueryService;
use Modules\Seo\Http\Requests\GetSeoMetaRequest;
use Modules\Seo\Http\Requests\SaveSeoMetaRequest;
use Modules\Seo\Http\Requests\StoreRedirectRequest;
use Modules\Seo\Http\Resources\RedirectResource;
use Modules\Seo\Http\Resources\SeoMetaResource;

/**
 * SEO API Controller.
 *
 * Follows Clean Architecture principles:
 * - No validation logic (delegated to Form Requests)
 * - No business logic (delegated to Services)
 * - No direct Model usage (uses Repository Pattern via Services)
 */
class SeoController extends BaseController
{
    public function __construct(
        protected SeoQueryService $queryService,
        protected SeoCommandService $commandService
    ) {
    }

    public function getSeoMeta(GetSeoMetaRequest $request): JsonResponse
    {
        $data = $request->validated();
        $meta = $this->queryService->getMetaForModel($data['type'], $data['id'], $data['locale'] ?? null);
        return $meta ? $this->success(new SeoMetaResource($meta)) : $this->notFound();
    }

    public function saveSeoMeta(SaveSeoMetaRequest $request): JsonResponse
    {
        $meta = $this->commandService->saveOrUpdateMeta($request->validated());
        return $this->success(new SeoMetaResource($meta));
    }

    public function redirects(Request $request): JsonResponse
    {
        $redirects = $this->queryService->getRedirects($request->integer('per_page', 20));
        return $this->paginated(RedirectResource::collection($redirects)->resource);
    }

    public function storeRedirect(StoreRedirectRequest $request): JsonResponse
    {
        $redirect = $this->commandService->createRedirect($request->validated());
        return $this->created(new RedirectResource($redirect));
    }

    public function updateRedirect(Request $request, string $id): JsonResponse
    {
        $redirect = $this->queryService->findRedirect($id);
        if (!$redirect) return $this->notFound();

        $updated = $this->commandService->updateRedirect($id, $request->only(['target_path', 'status_code', 'is_active', 'is_regex', 'notes']));
        return $this->success(new RedirectResource($updated));
    }

    public function destroyRedirect(string $id): JsonResponse
    {
        $redirect = $this->queryService->findRedirect($id);
        if (!$redirect) return $this->notFound();

        $this->commandService->deleteRedirect($id);
        return $this->success(null, 'Redirect deleted');
    }
}
