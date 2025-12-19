<?php

declare(strict_types=1);

namespace Modules\Testimonials\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Testimonials\Application\Services\TestimonialCommandService;
use Modules\Testimonials\Application\Services\TestimonialQueryService;
use Modules\Testimonials\Http\Requests\ImportTestimonialsRequest;
use Modules\Testimonials\Http\Requests\LinkEntityRequest;
use Modules\Testimonials\Http\Requests\RejectTestimonialRequest;
use Modules\Testimonials\Http\Requests\ReorderTestimonialsRequest;
use Modules\Testimonials\Http\Requests\RequestTestimonialRequest;
use Modules\Testimonials\Http\Requests\StoreTestimonialRequest;
use Modules\Testimonials\Http\Requests\UnlinkEntityRequest;
use Modules\Testimonials\Http\Requests\UpdateTestimonialRequest;
use Modules\Testimonials\Http\Resources\TestimonialResource;

/**
 * Testimonial Management Controller.
 *
 * Handles CRUD and workflow operations for testimonials.
 *
 * @package Modules\Testimonials\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class TestimonialManagementController extends BaseController
{
    public function __construct(
        private readonly TestimonialQueryService $queryService,
        private readonly TestimonialCommandService $commandService
    ) {}

    public function store(StoreTestimonialRequest $request): JsonResponse
    {
        try {
            $testimonial = $this->commandService->create($request->validated());

            return $this->created(new TestimonialResource($testimonial), 'Testimonial created');
        } catch (\Throwable $e) {
            return $this->error('Failed to create testimonial: ' . $e->getMessage());
        }
    }

    public function update(UpdateTestimonialRequest $request, string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $updated = $this->commandService->update($testimonial, $request->validated());

            return $this->success(new TestimonialResource($updated), 'Testimonial updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to update testimonial: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $this->commandService->delete($testimonial);

            return $this->success(null, 'Testimonial deleted');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete testimonial: ' . $e->getMessage());
        }
    }

    public function forceDestroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->commandService->forceDelete($id);

            return $deleted
                ? $this->success(null, 'Testimonial permanently deleted')
                : $this->notFound('Testimonial not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete testimonial: ' . $e->getMessage());
        }
    }

    public function restore(string $id): JsonResponse
    {
        try {
            $testimonial = $this->commandService->restore($id);

            return $testimonial
                ? $this->success(new TestimonialResource($testimonial), 'Testimonial restored')
                : $this->notFound('Testimonial not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to restore testimonial: ' . $e->getMessage());
        }
    }

    public function submitForReview(string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $submitted = $this->commandService->submitForReview($testimonial);

            return $this->success(new TestimonialResource($submitted), 'Submitted for review');
        } catch (\Throwable $e) {
            return $this->error('Failed to submit: ' . $e->getMessage());
        }
    }

    public function startReview(string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $reviewing = $this->commandService->startReview($testimonial);

            return $this->success(new TestimonialResource($reviewing), 'Review started');
        } catch (\Throwable $e) {
            return $this->error('Failed to start review: ' . $e->getMessage());
        }
    }

    public function approve(string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $approved = $this->commandService->approve($testimonial);

            return $this->success(new TestimonialResource($approved), 'Testimonial approved');
        } catch (\Throwable $e) {
            return $this->error('Failed to approve: ' . $e->getMessage());
        }
    }

    public function reject(RejectTestimonialRequest $request, string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $rejected = $this->commandService->reject($testimonial, $request->validated()['reason']);

            return $this->success(new TestimonialResource($rejected), 'Testimonial rejected');
        } catch (\Throwable $e) {
            return $this->error('Failed to reject: ' . $e->getMessage());
        }
    }

    public function publish(string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $published = $this->commandService->publish($testimonial);

            return $this->success(new TestimonialResource($published), 'Testimonial published');
        } catch (\Throwable $e) {
            return $this->error('Failed to publish: ' . $e->getMessage());
        }
    }

    public function unpublish(string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $unpublished = $this->commandService->unpublish($testimonial);

            return $this->success(new TestimonialResource($unpublished), 'Testimonial unpublished');
        } catch (\Throwable $e) {
            return $this->error('Failed to unpublish: ' . $e->getMessage());
        }
    }

    public function archive(string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $archived = $this->commandService->archive($testimonial);

            return $this->success(new TestimonialResource($archived), 'Testimonial archived');
        } catch (\Throwable $e) {
            return $this->error('Failed to archive: ' . $e->getMessage());
        }
    }

    public function feature(string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $featured = $this->commandService->feature($testimonial);

            return $this->success(new TestimonialResource($featured), 'Testimonial featured');
        } catch (\Throwable $e) {
            return $this->error('Failed to feature: ' . $e->getMessage());
        }
    }

    public function unfeature(string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $unfeatured = $this->commandService->unfeature($testimonial);

            return $this->success(new TestimonialResource($unfeatured), 'Testimonial unfeatured');
        } catch (\Throwable $e) {
            return $this->error('Failed to unfeature: ' . $e->getMessage());
        }
    }

    public function verifyClient(string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $verified = $this->commandService->verifyClient($testimonial);

            return $this->success(new TestimonialResource($verified), 'Client verified');
        } catch (\Throwable $e) {
            return $this->error('Failed to verify client: ' . $e->getMessage());
        }
    }

    public function requestTestimonial(RequestTestimonialRequest $request): JsonResponse
    {
        try {
            $result = $this->commandService->requestFromClient($request->validated());

            return $this->success($result, 'Testimonial request sent');
        } catch (\Throwable $e) {
            return $this->error('Failed to request testimonial: ' . $e->getMessage());
        }
    }

    public function linkEntity(LinkEntityRequest $request, string $id): JsonResponse
    {
        try {
            $testimonial = $this->queryService->find($id);

            if (!$testimonial) {
                return $this->notFound('Testimonial not found');
            }

            $this->commandService->linkEntity($id, $request->validated());

            return $this->success(null, 'Entity linked');
        } catch (\Throwable $e) {
            return $this->error('Failed to link entity: ' . $e->getMessage());
        }
    }

    public function unlinkEntity(UnlinkEntityRequest $request, string $id): JsonResponse
    {
        try {
            $this->commandService->unlinkEntity($id, $request->validated());

            return $this->success(null, 'Entity unlinked');
        } catch (\Throwable $e) {
            return $this->error('Failed to unlink entity: ' . $e->getMessage());
        }
    }

    public function reorder(ReorderTestimonialsRequest $request): JsonResponse
    {
        try {
            $this->commandService->reorder($request->validated()['order']);

            return $this->success(null, 'Testimonials reordered');
        } catch (\Throwable $e) {
            return $this->error('Failed to reorder: ' . $e->getMessage());
        }
    }

    public function import(ImportTestimonialsRequest $request): JsonResponse
    {
        try {
            $result = $this->commandService->import($request->validated());

            return $this->success($result, 'Testimonials imported');
        } catch (\Throwable $e) {
            return $this->error('Failed to import: ' . $e->getMessage());
        }
    }
}
