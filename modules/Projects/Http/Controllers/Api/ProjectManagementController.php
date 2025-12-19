<?php

declare(strict_types=1);

namespace Modules\Projects\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Projects\Application\Services\ProjectCommandService;
use Modules\Projects\Application\Services\ProjectQueryService;
use Modules\Projects\Http\Requests\CreateProjectRequest;
use Modules\Projects\Http\Requests\CreateTranslationRequest;
use Modules\Projects\Http\Requests\DuplicateProjectRequest;
use Modules\Projects\Http\Requests\RejectProjectRequest;
use Modules\Projects\Http\Requests\ScheduleProjectRequest;
use Modules\Projects\Http\Requests\UpdateProjectRequest;
use Modules\Projects\Http\Resources\ProjectResource;

/**
 * Project Management Controller.
 *
 * Handles CRUD and workflow operations for projects.
 *
 * @package Modules\Projects\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ProjectManagementController extends BaseController
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
     * Store a newly created project.
     */
    public function store(CreateProjectRequest $request): JsonResponse
    {
        try {
            $project = $this->commandService->create($request->validated());

            return $this->created(new ProjectResource($project), 'Project created');
        } catch (\Throwable $e) {
            return $this->error('Failed to create project: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified project.
     */
    public function update(UpdateProjectRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->update($project, $request->validated());

            return $this->success(new ProjectResource($project), 'Project updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to update project: ' . $e->getMessage());
        }
    }

    /**
     * Delete the specified project.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $this->commandService->delete($project);

            return $this->success(null, 'Project deleted');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete project: ' . $e->getMessage());
        }
    }

    /**
     * Force delete project permanently.
     */
    public function forceDestroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->commandService->forceDelete($id);

            return $deleted
                ? $this->success(null, 'Project permanently deleted')
                : $this->notFound('Project not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete project: ' . $e->getMessage());
        }
    }

    /**
     * Restore soft-deleted project.
     */
    public function restore(string $id): JsonResponse
    {
        try {
            $project = $this->commandService->restore($id);

            return $project
                ? $this->success(new ProjectResource($project), 'Project restored')
                : $this->notFound('Project not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to restore project: ' . $e->getMessage());
        }
    }

    /**
     * Publish the specified project.
     */
    public function publish(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->publish($project);

            return $this->success(new ProjectResource($project), 'Project published');
        } catch (\Throwable $e) {
            return $this->error('Failed to publish project: ' . $e->getMessage());
        }
    }

    /**
     * Unpublish the specified project.
     */
    public function unpublish(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->unpublish($project);

            return $this->success(new ProjectResource($project), 'Project unpublished');
        } catch (\Throwable $e) {
            return $this->error('Failed to unpublish project: ' . $e->getMessage());
        }
    }

    /**
     * Schedule publishing for the project.
     */
    public function schedule(ScheduleProjectRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->schedule($project, new \DateTime($request->scheduled_at));

            return $this->success(new ProjectResource($project), 'Project scheduled');
        } catch (\Throwable $e) {
            return $this->error('Failed to schedule project: ' . $e->getMessage());
        }
    }

    /**
     * Cancel scheduled publishing.
     */
    public function cancelSchedule(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->cancelSchedule($project);

            return $this->success(new ProjectResource($project), 'Schedule cancelled');
        } catch (\Throwable $e) {
            return $this->error('Failed to cancel schedule: ' . $e->getMessage());
        }
    }

    /**
     * Save project as draft.
     */
    public function saveDraft(Request $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->saveDraft($project, $request->all());

            return $this->success(new ProjectResource($project), 'Draft saved');
        } catch (\Throwable $e) {
            return $this->error('Failed to save draft: ' . $e->getMessage());
        }
    }

    /**
     * Submit project for review.
     */
    public function submitForReview(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->submitForReview($project);

            return $this->success(new ProjectResource($project), 'Submitted for review');
        } catch (\Throwable $e) {
            return $this->error('Failed to submit for review: ' . $e->getMessage());
        }
    }

    /**
     * Start reviewing the project.
     */
    public function startReview(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->startReview($project);

            return $this->success(new ProjectResource($project), 'Review started');
        } catch (\Throwable $e) {
            return $this->error('Failed to start review: ' . $e->getMessage());
        }
    }

    /**
     * Approve the project.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->approve($project, $request->notes);

            return $this->success(new ProjectResource($project), 'Project approved');
        } catch (\Throwable $e) {
            return $this->error('Failed to approve project: ' . $e->getMessage());
        }
    }

    /**
     * Reject the project.
     */
    public function reject(RejectProjectRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->reject($project, $request->reason);

            return $this->success(new ProjectResource($project), 'Project rejected');
        } catch (\Throwable $e) {
            return $this->error('Failed to reject project: ' . $e->getMessage());
        }
    }

    /**
     * Archive the project.
     */
    public function archive(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->archive($project);

            return $this->success(new ProjectResource($project), 'Project archived');
        } catch (\Throwable $e) {
            return $this->error('Failed to archive project: ' . $e->getMessage());
        }
    }

    /**
     * Restore from archive.
     */
    public function unarchive(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->unarchive($project);

            return $this->success(new ProjectResource($project), 'Project unarchived');
        } catch (\Throwable $e) {
            return $this->error('Failed to unarchive project: ' . $e->getMessage());
        }
    }

    /**
     * Feature the project.
     */
    public function feature(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->feature($project);

            return $this->success(new ProjectResource($project), 'Project featured');
        } catch (\Throwable $e) {
            return $this->error('Failed to feature project: ' . $e->getMessage());
        }
    }

    /**
     * Unfeature the project.
     */
    public function unfeature(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->unfeature($project);

            return $this->success(new ProjectResource($project), 'Project unfeatured');
        } catch (\Throwable $e) {
            return $this->error('Failed to unfeature project: ' . $e->getMessage());
        }
    }

    /**
     * Duplicate/clone the project.
     */
    public function duplicate(DuplicateProjectRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $cloned = $this->commandService->duplicate($project, $request->new_slug);

            return $this->created(new ProjectResource($cloned), 'Project duplicated');
        } catch (\Throwable $e) {
            return $this->error('Failed to duplicate project: ' . $e->getMessage());
        }
    }

    /**
     * Create translated version.
     */
    public function createTranslation(CreateTranslationRequest $request, string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $translation = $this->commandService->createTranslation($project, $request->validated());

            return $this->created($translation, 'Translation created');
        } catch (\Throwable $e) {
            return $this->error('Failed to create translation: ' . $e->getMessage());
        }
    }

    /**
     * Restore a specific revision.
     */
    public function restoreRevision(string $id, string $revisionId): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $project = $this->commandService->restoreRevision($project, $revisionId);

            return $this->success(new ProjectResource($project), 'Revision restored');
        } catch (\Throwable $e) {
            return $this->error('Failed to restore revision: ' . $e->getMessage());
        }
    }

    /**
     * Export project as PDF.
     */
    public function exportPdf(string $id): JsonResponse
    {
        try {
            $project = $this->queryService->find($id);

            if (!$project) {
                return $this->notFound('Project not found');
            }

            $pdfUrl = $this->commandService->exportPdf($project);

            return $this->success(['url' => $pdfUrl]);
        } catch (\Throwable $e) {
            return $this->error('Failed to export PDF: ' . $e->getMessage());
        }
    }
}
