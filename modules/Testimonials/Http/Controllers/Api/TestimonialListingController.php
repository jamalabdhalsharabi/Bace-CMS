<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Testimonials\Application\Services\TestimonialQueryService;
use Modules\Testimonials\Http\Resources\TestimonialResource;

/**
 * Testimonial Listing Controller.
 *
 * Handles all read-only operations for testimonials.
 *
 * @package Modules\Testimonials\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class TestimonialListingController extends BaseController
{
    public function __construct(
        private readonly TestimonialQueryService $queryService
    ) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $testimonials = $this->queryService->getPaginated(
                filters: ['active' => true, 'featured' => $request->boolean('featured')],
                perPage: $request->integer('per_page', 10)
            );

            return $this->paginated(TestimonialResource::collection($testimonials)->resource);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve testimonials: ' . $e->getMessage());
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            return $testimonial
                ? $this->success(new TestimonialResource($testimonial))
                : $this->notFound('Testimonial not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve testimonial: ' . $e->getMessage());
        }
    }

    public function featured(Request $request): JsonResponse
    {
        try {
            $testimonials = $this->queryService->getFeatured($request->integer('limit', 6));

            return $this->success(TestimonialResource::collection($testimonials));
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve featured testimonials: ' . $e->getMessage());
        }
    }

    public function ratingStats(): JsonResponse
    {
        try {
            $stats = $this->queryService->getRatingStats();

            return $this->success($stats);
        } catch (\Throwable $e) {
            return $this->error('Failed to retrieve rating stats: ' . $e->getMessage());
        }
    }
}
