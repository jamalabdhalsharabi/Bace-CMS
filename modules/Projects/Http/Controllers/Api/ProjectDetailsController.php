<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Projects\Application\Services\ProjectCommandService;
use Modules\Projects\Application\Services\ProjectQueryService;
use Modules\Projects\Http\Requests\AddCaseStudyRequest;
use Modules\Projects\Http\Requests\AddMetricsRequest;
use Modules\Projects\Http\Requests\CreateComparisonRequest;
use Modules\Projects\Http\Requests\GalleryImageRequest;
use Modules\Projects\Http\Requests\LinkRelatedRequest;
use Modules\Projects\Http\Requests\LinkTestimonialRequest;
use Modules\Projects\Http\Requests\ReorderGalleryRequest;
use Modules\Projects\Http\Requests\RequestTestimonialRequest;

/**
 * Project Details Controller.
 *
 * Handles project details operations including gallery, case studies,
 * comparisons, metrics, and relationships.
 *
 * @package Modules\Projects\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ProjectDetailsController extends BaseController
{
    /**
     * @param ProjectQueryService $queryService Service for project read operations
     * @param ProjectCommandService $commandService Service for project write operations
     */
    public function __construct(
        private readonly ProjectQueryService $queryService,
        private readonly ProjectCommandService $commandService
    ) {}

    /**
     * Add image to gallery.
     */
    public function addGalleryImage(GalleryImageRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $this->commandService->addGalleryImage($project, $request->media_id, $request->integer('sort_order', 0));

            return $this->success(null, 'Image added to gallery');
        } catch (\Throwable $e) {
            return $this->error('Failed to add image: ' . $e->getMessage());
        }
    }

    /**
     * Remove image from gallery.
     */
    public function removeGalleryImage(string $id, string $mediaId): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $this->commandService->removeGalleryImage($project, $mediaId);

            return $this->success(null, 'Image removed from gallery');
        } catch (\Throwable $e) {
            return $this->error('Failed to remove image: ' . $e->getMessage());
        }
    }

    /**
     * Reorder gallery images.
     */
    public function reorderGallery(ReorderGalleryRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $this->commandService->reorderGallery($project, $request->order);

            return $this->success(null, 'Gallery reordered');
        } catch (\Throwable $e) {
            return $this->error('Failed to reorder gallery: ' . $e->getMessage());
        }
    }

    /**
     * Create before/after comparison.
     */
    public function createComparison(CreateComparisonRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $comparison = $this->commandService->createComparison($project, $request->validated());

            return $this->created($comparison, 'Comparison created');
        } catch (\Throwable $e) {
            return $this->error('Failed to create comparison: ' . $e->getMessage());
        }
    }

    /**
     * Add case study.
     */
    public function addCaseStudy(AddCaseStudyRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $caseStudy = $this->commandService->addCaseStudy($project, $request->validated());

            return $this->created($caseStudy, 'Case study added');
        } catch (\Throwable $e) {
            return $this->error('Failed to add case study: ' . $e->getMessage());
        }
    }

    /**
     * Update case study.
     */
    public function updateCaseStudy(Request $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $caseStudy = $this->commandService->updateCaseStudy($project, $request->all());

            return $this->success($caseStudy, 'Case study updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to update case study: ' . $e->getMessage());
        }
    }

    /**
     * Add result metrics.
     */
    public function addMetrics(AddMetricsRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $this->commandService->addMetrics($project, $request->metrics);

            return $this->success(null, 'Metrics added');
        } catch (\Throwable $e) {
            return $this->error('Failed to add metrics: ' . $e->getMessage());
        }
    }

    /**
     * Link technologies.
     */
    public function linkTechnologies(LinkRelatedRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $this->commandService->linkTechnologies($project, $request->ids);

            return $this->success(null, 'Technologies linked');
        } catch (\Throwable $e) {
            return $this->error('Failed to link technologies: ' . $e->getMessage());
        }
    }

    /**
     * Link industries.
     */
    public function linkIndustries(LinkRelatedRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $this->commandService->linkIndustries($project, $request->ids);

            return $this->success(null, 'Industries linked');
        } catch (\Throwable $e) {
            return $this->error('Failed to link industries: ' . $e->getMessage());
        }
    }

    /**
     * Link testimonial.
     */
    public function linkTestimonial(LinkTestimonialRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $this->commandService->linkTestimonial($project, $request->validated()['testimonial_id']);

            return $this->success(null, 'Testimonial linked');
        } catch (\Throwable $e) {
            return $this->error('Failed to link testimonial: ' . $e->getMessage());
        }
    }

    /**
     * Request testimonial from client.
     */
    public function requestTestimonial(RequestTestimonialRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $this->commandService->requestTestimonial($project, $request->client_email, $request->message);

            return $this->success(null, 'Testimonial request sent');
        } catch (\Throwable $e) {
            return $this->error('Failed to request testimonial: ' . $e->getMessage());
        }
    }

    /**
     * Link related projects.
     */
    public function linkRelated(LinkRelatedRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $this->commandService->linkRelated($project, $request->ids);

            return $this->success(null, 'Related projects linked');
        } catch (\Throwable $e) {
            return $this->error('Failed to link related projects: ' . $e->getMessage());
        }
    }
}
