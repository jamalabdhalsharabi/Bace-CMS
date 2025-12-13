<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Projects\Application\Services\ProjectCommandService;
use Modules\Projects\Application\Services\ProjectQueryService;
use Modules\Projects\Http\Requests\CreateProjectRequest;
use Modules\Projects\Http\Requests\UpdateProjectRequest;
use Modules\Projects\Http\Requests\ScheduleProjectRequest;
use Modules\Projects\Http\Requests\RejectProjectRequest;
use Modules\Projects\Http\Requests\DuplicateProjectRequest;
use Modules\Projects\Http\Requests\CreateTranslationRequest;
use Modules\Projects\Http\Requests\GalleryImageRequest;
use Modules\Projects\Http\Requests\ReorderGalleryRequest;
use Modules\Projects\Http\Requests\LinkRelatedRequest;
use Modules\Projects\Http\Requests\AddCaseStudyRequest;
use Modules\Projects\Http\Requests\AddMetricsRequest;
use Modules\Projects\Http\Requests\CreateComparisonRequest;
use Modules\Projects\Http\Requests\RequestTestimonialRequest;
use Modules\Projects\Http\Resources\ProjectResource;

class ProjectController extends BaseController
{
    public function __construct(
        protected ProjectQueryService $queryService,
        protected ProjectCommandService $commandService
    ) {
    }

    /**
     * Display a paginated listing of portfolio projects.
     *
     * Supports filtering by status, featured flag, and type.
     *
     * @param Request $request The incoming HTTP request containing filter parameters
     * @return JsonResponse Paginated list of projects wrapped in ProjectResource
     */
    public function index(Request $request): JsonResponse
    {
        $projects = $this->queryService->list(
            $request->only(['status', 'featured', 'type']),
            $request->integer('per_page', 12)
        );
        return $this->paginated(ProjectResource::collection($projects)->resource);
    }

    /**
     * Display the specified project by its UUID.
     *
     * @param string $id The UUID of the project to retrieve
     * @return JsonResponse The project data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        return $this->success(new ProjectResource($project));
    }

    /**
     * Display the specified project by its URL slug.
     *
     * @param string $slug The URL-friendly slug of the project
     * @return JsonResponse The project data or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $project = $this->queryService->findBySlug($slug);
        if (!$project) return $this->notFound('Project not found');
        return $this->success(new ProjectResource($project));
    }

    /**
     * Store a newly created project in the database.
     *
     * @param CreateProjectRequest $request The validated request containing project data
     * @return JsonResponse The newly created project (HTTP 201)
     */
    public function store(CreateProjectRequest $request): JsonResponse
    {
        $project = $this->queryService->create($request->validated());
        return $this->created(new ProjectResource($project));
    }

    /**
     * Update the specified project in the database.
     *
     * @param UpdateProjectRequest $request The validated request containing updated data
     * @param string $id The UUID of the project to update
     * @return JsonResponse The updated project or 404 error
     */
    public function update(UpdateProjectRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->queryService->update($project, $request->validated());
        return $this->success(new ProjectResource($project));
    }

    /**
     * Delete the specified project.
     *
     * @param string $id The UUID of the project to delete
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->queryService->delete($project);
        return $this->success(null, 'Project deleted');
    }

    /**
     * Publish the specified project, making it publicly visible.
     */
    public function publish(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->publish($project);
        return $this->success(new ProjectResource($project));
    }

    /**
     * Unpublish the specified project.
     */
    public function unpublish(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->unpublish($project);
        return $this->success(new ProjectResource($project));
    }

    /**
     * Schedule publishing for the project.
     */
    public function schedule(ScheduleProjectRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->schedule($project, new \DateTime($request->scheduled_at));
        return $this->success(new ProjectResource($project));
    }

    /**
     * Cancel scheduled publishing.
     */
    public function cancelSchedule(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->cancelSchedule($project);
        return $this->success(new ProjectResource($project));
    }

    /**
     * Save project as draft.
     */
    public function saveDraft(Request $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->saveDraft($project, $request->all());
        return $this->success(new ProjectResource($project));
    }

    /**
     * Submit project for review.
     */
    public function submitForReview(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->submitForReview($project);
        return $this->success(new ProjectResource($project));
    }

    /**
     * Start reviewing the project.
     */
    public function startReview(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->startReview($project);
        return $this->success(new ProjectResource($project));
    }

    /**
     * Approve the project.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->approve($project, $request->notes);
        return $this->success(new ProjectResource($project));
    }

    /**
     * Reject the project.
     */
    public function reject(RejectProjectRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->reject($project, $request->reason);
        return $this->success(new ProjectResource($project));
    }

    /**
     * Archive the project.
     */
    public function archive(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->archive($project);
        return $this->success(new ProjectResource($project));
    }

    /**
     * Restore from archive.
     */
    public function unarchive(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->unarchive($project);
        return $this->success(new ProjectResource($project));
    }

    /**
     * Restore soft-deleted project.
     */
    public function restore(string $id): JsonResponse
    {
        $project = $this->commandService->restore($id);
        return $project ? $this->success(new ProjectResource($project)) : $this->notFound('Project not found');
    }

    /**
     * Force delete project permanently.
     */
    public function forceDestroy(string $id): JsonResponse
    {
        $project = \Modules\Projects\Domain\Models\Project::withTrashed()->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->commandService->forceDelete($project);
        return $this->success(null, 'Project permanently deleted');
    }

    /**
     * Feature the project.
     */
    public function feature(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->feature($project);
        return $this->success(new ProjectResource($project));
    }

    /**
     * Unfeature the project.
     */
    public function unfeature(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->unfeature($project);
        return $this->success(new ProjectResource($project));
    }

    /**
     * Duplicate/clone the project.
     */
    public function duplicate(DuplicateProjectRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $cloned = $this->commandService->duplicate($project, $request->new_slug);
        return $this->created(new ProjectResource($cloned));
    }

    /**
     * Create translated version.
     */
    public function createTranslation(CreateTranslationRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $translation = $this->commandService->createTranslation($project, $request->validated());
        return $this->created($translation);
    }

    /**
     * Add image to gallery.
     */
    public function addGalleryImage(GalleryImageRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->commandService->addGalleryImage($project, $request->media_id, $request->integer('sort_order', 0));
        return $this->success(null, 'Image added to gallery');
    }

    /**
     * Remove image from gallery.
     */
    public function removeGalleryImage(string $id, string $mediaId): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->commandService->removeGalleryImage($project, $mediaId);
        return $this->success(null, 'Image removed from gallery');
    }

    /**
     * Reorder gallery images.
     */
    public function reorderGallery(ReorderGalleryRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->commandService->reorderGallery($project, $request->order);
        return $this->success(null, 'Gallery reordered');
    }

    /**
     * Create before/after comparison.
     */
    public function createComparison(CreateComparisonRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $comparison = $this->commandService->createComparison($project, $request->validated());
        return $this->created($comparison);
    }

    /**
     * Add case study.
     */
    public function addCaseStudy(AddCaseStudyRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $caseStudy = $this->commandService->addCaseStudy($project, $request->validated());
        return $this->created($caseStudy);
    }

    /**
     * Update case study.
     */
    public function updateCaseStudy(Request $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $caseStudy = $this->commandService->updateCaseStudy($project, $request->all());
        return $this->success($caseStudy);
    }

    /**
     * Add result metrics.
     */
    public function addMetrics(AddMetricsRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->commandService->addMetrics($project, $request->metrics);
        return $this->success(null, 'Metrics added');
    }

    /**
     * Link technologies.
     */
    public function linkTechnologies(LinkRelatedRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->commandService->linkTechnologies($project, $request->ids);
        return $this->success(null, 'Technologies linked');
    }

    /**
     * Link industries.
     */
    public function linkIndustries(LinkRelatedRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->commandService->linkIndustries($project, $request->ids);
        return $this->success(null, 'Industries linked');
    }

    /**
     * Link testimonial.
     */
    public function linkTestimonial(Request $request, string $id): JsonResponse
    {
        $request->validate(['testimonial_id' => 'required|uuid']);
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->commandService->linkTestimonial($project, $request->testimonial_id);
        return $this->success(null, 'Testimonial linked');
    }

    /**
     * Request testimonial from client.
     */
    public function requestTestimonial(RequestTestimonialRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->commandService->requestTestimonial($project, $request->client_email, $request->message);
        return $this->success(null, 'Testimonial request sent');
    }

    /**
     * Link related projects.
     */
    public function linkRelated(LinkRelatedRequest $request, string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $this->commandService->linkRelated($project, $request->ids);
        return $this->success(null, 'Related projects linked');
    }

    /**
     * Export project as PDF.
     */
    public function exportPdf(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $pdfUrl = $this->commandService->exportPdf($project);
        return $this->success(['url' => $pdfUrl]);
    }

    /**
     * Get featured projects.
     */
    public function featured(Request $request): JsonResponse
    {
        $projects = $this->queryService->getFeatured($request->integer('limit', 6));
        return $this->success(ProjectResource::collection($projects));
    }

    /**
     * Get revision history.
     */
    public function revisions(string $id): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $revisions = $this->queryService->getRevisions($project);
        return $this->success($revisions);
    }

    /**
     * Restore a specific revision.
     */
    public function restoreRevision(string $id, string $revisionId): JsonResponse
    {
        $project = $this->queryService->find($id);
        if (!$project) return $this->notFound('Project not found');
        $project = $this->commandService->restoreRevision($project, $revisionId);
        return $this->success(new ProjectResource($project));
    }
}
