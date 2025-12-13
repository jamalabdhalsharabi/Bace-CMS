<?php

declare(strict_types=1);

namespace Modules\Seo\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Seo\Domain\Models\Redirect;
use Modules\Seo\Domain\Models\SeoMeta;
use Modules\Seo\Http\Resources\RedirectResource;
use Modules\Seo\Http\Resources\SeoMetaResource;

/**
 * Class SeoController
 *
 * API controller for SEO management including meta tags
 * and URL redirects.
 *
 * @package Modules\Seo\Http\Controllers\Api
 */
class SeoController extends BaseController
{
    /**
     * Get SEO meta for a specific model.
     *
     * @param Request $request The request with type, id, and optional locale
     * @return JsonResponse The SEO meta or 404 error
     */
    public function getSeoMeta(Request $request): JsonResponse
    {
        $request->validate([
            'type' => 'required|string',
            'id' => 'required|uuid',
            'locale' => 'nullable|string|size:2',
        ]);

        $meta = SeoMeta::forModel($request->type, $request->id)
            ->locale($request->locale ?? app()->getLocale())
            ->first();

        return $meta ? $this->success(new SeoMetaResource($meta)) : $this->notFound();
    }

    /**
     * Save or update SEO meta for a model.
     *
     * @param Request $request The request with SEO meta fields
     * @return JsonResponse The saved SEO meta
     */
    public function saveSeoMeta(Request $request): JsonResponse
    {
        $data = $request->validate([
            'seoable_type' => 'required|string',
            'seoable_id' => 'required|uuid',
            'locale' => 'nullable|string|size:2',
            'meta_title' => 'nullable|string|max:60',
            'meta_description' => 'nullable|string|max:160',
            'meta_keywords' => 'nullable|string|max:255',
            'canonical_url' => 'nullable|url',
            'robots' => 'nullable|string|max:50',
            'og_title' => 'nullable|string|max:100',
            'og_description' => 'nullable|string|max:200',
            'og_image' => 'nullable|url',
        ]);

        $meta = SeoMeta::updateOrCreate(
            [
                'seoable_type' => $data['seoable_type'],
                'seoable_id' => $data['seoable_id'],
                'locale' => $data['locale'] ?? app()->getLocale(),
            ],
            $data
        );

        return $this->success(new SeoMetaResource($meta));
    }

    /**
     * Display paginated list of URL redirects.
     *
     * @param Request $request The request with pagination options
     * @return JsonResponse Paginated list of redirects
     */
    public function redirects(Request $request): JsonResponse
    {
        $redirects = Redirect::orderByDesc('created_at')->paginate($request->integer('per_page', 20));
        return $this->paginated(RedirectResource::collection($redirects)->resource);
    }

    /**
     * Create a new URL redirect.
     *
     * @param Request $request The request with redirect data
     * @return JsonResponse The created redirect (HTTP 201)
     */
    public function storeRedirect(Request $request): JsonResponse
    {
        $data = $request->validate([
            'source_path' => 'required|string|max:500|unique:redirects,source_path',
            'target_path' => 'required|string|max:500',
            'status_code' => 'nullable|integer|in:301,302,307,308',
            'is_regex' => 'nullable|boolean',
            'notes' => 'nullable|string|max:500',
        ]);

        $redirect = Redirect::create([
            'source_path' => $data['source_path'],
            'target_path' => $data['target_path'],
            'status_code' => $data['status_code'] ?? 301,
            'is_active' => true,
            'is_regex' => $data['is_regex'] ?? false,
            'notes' => $data['notes'] ?? null,
        ]);

        return $this->created(new RedirectResource($redirect));
    }

    /**
     * Update an existing redirect.
     *
     * @param Request $request The request with updated redirect data
     * @param string $id The UUID of the redirect
     * @return JsonResponse The updated redirect or 404 error
     */
    public function updateRedirect(Request $request, string $id): JsonResponse
    {
        $redirect = Redirect::find($id);
        if (!$redirect) return $this->notFound();

        $redirect->update($request->only(['target_path', 'status_code', 'is_active', 'is_regex', 'notes']));
        return $this->success(new RedirectResource($redirect->fresh()));
    }

    /**
     * Delete a redirect.
     *
     * @param string $id The UUID of the redirect
     * @return JsonResponse Success message or 404 error
     */
    public function destroyRedirect(string $id): JsonResponse
    {
        $redirect = Redirect::find($id);
        if (!$redirect) return $this->notFound();
        $redirect->delete();
        return $this->success(null, 'Redirect deleted');
    }
}
