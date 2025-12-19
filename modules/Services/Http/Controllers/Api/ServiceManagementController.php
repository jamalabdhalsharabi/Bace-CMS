<?php

declare(strict_types=1);

namespace Modules\Services\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Services\Application\Services\ServiceCommandService;
use Modules\Services\Application\Services\ServiceQueryService;
use Modules\Services\Http\Requests\AttachRelatedRequest;
use Modules\Services\Http\Requests\CloneServiceRequest;
use Modules\Services\Http\Requests\CreateServiceTranslationRequest;
use Modules\Services\Http\Requests\MediaIdsRequest;
use Modules\Services\Http\Requests\ReorderServicesRequest;
use Modules\Services\Http\Requests\RestoreRevisionRequest;
use Modules\Services\Http\Requests\ScheduleServiceRequest;
use Modules\Services\Http\Requests\StoreServiceRequest;
use Modules\Services\Http\Requests\SyncCategoriesRequest;
use Modules\Services\Http\Resources\ServiceResource;

/**
 * Service Management Controller.
 *
 * Handles CRUD and workflow operations for services.
 *
 * @package Modules\Services\Http\Controllers\Api
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class ServiceManagementController extends BaseController
{
    public function __construct(
        private readonly ServiceQueryService $queryService,
        private readonly ServiceCommandService $commandService
    ) {}

    public function store(StoreServiceRequest $request): JsonResponse
    {
        try {
            $service = $this->commandService->create($request->validated());

            return $this->created(new ServiceResource($service), 'Service created');
        } catch (\Throwable $e) {
            return $this->error('Failed to create service: ' . $e->getMessage());
        }
    }

    public function update(Request $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->update($service, $request->all());

            return $this->success(new ServiceResource($service), 'Service updated');
        } catch (\Throwable $e) {
            return $this->error('Failed to update service: ' . $e->getMessage());
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $this->commandService->delete($service);

            return $this->success(null, 'Service deleted');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete service: ' . $e->getMessage());
        }
    }

    public function forceDestroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->commandService->forceDelete($id);

            return $deleted
                ? $this->success(null, 'Service permanently deleted')
                : $this->notFound('Service not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to delete service: ' . $e->getMessage());
        }
    }

    public function restore(string $id): JsonResponse
    {
        try {
            $service = $this->commandService->restore($id);

            return $service
                ? $this->success(new ServiceResource($service), 'Service restored')
                : $this->notFound('Service not found');
        } catch (\Throwable $e) {
            return $this->error('Failed to restore service: ' . $e->getMessage());
        }
    }

    public function saveDraft(Request $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->saveDraft($service, $request->all());

            return $this->success(new ServiceResource($service), 'Draft saved');
        } catch (\Throwable $e) {
            return $this->error('Failed to save draft: ' . $e->getMessage());
        }
    }

    public function submitForReview(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->submitForReview($service);

            return $this->success(new ServiceResource($service), 'Submitted for review');
        } catch (\Throwable $e) {
            return $this->error('Failed to submit: ' . $e->getMessage());
        }
    }

    public function startReview(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->startReview($service, auth()->id());

            return $this->success(new ServiceResource($service), 'Review started');
        } catch (\Throwable $e) {
            return $this->error('Failed to start review: ' . $e->getMessage());
        }
    }

    public function approve(Request $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->approve($service, $request->notes);

            return $this->success(new ServiceResource($service), 'Service approved');
        } catch (\Throwable $e) {
            return $this->error('Failed to approve: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->reject($service, $request->notes);

            return $this->success(new ServiceResource($service), 'Service rejected');
        } catch (\Throwable $e) {
            return $this->error('Failed to reject: ' . $e->getMessage());
        }
    }

    public function publish(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->publish($service);

            return $this->success(new ServiceResource($service), 'Service published');
        } catch (\Throwable $e) {
            return $this->error('Failed to publish: ' . $e->getMessage());
        }
    }

    public function schedule(ScheduleServiceRequest $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->schedule($service, new \DateTime($request->validated()['scheduled_at']));

            return $this->success(new ServiceResource($service), 'Service scheduled');
        } catch (\Throwable $e) {
            return $this->error('Failed to schedule: ' . $e->getMessage());
        }
    }

    public function cancelSchedule(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->cancelSchedule($service);

            return $this->success(new ServiceResource($service), 'Schedule cancelled');
        } catch (\Throwable $e) {
            return $this->error('Failed to cancel schedule: ' . $e->getMessage());
        }
    }

    public function unpublish(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->unpublish($service);

            return $this->success(new ServiceResource($service), 'Service unpublished');
        } catch (\Throwable $e) {
            return $this->error('Failed to unpublish: ' . $e->getMessage());
        }
    }

    public function archive(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->archive($service);

            return $this->success(new ServiceResource($service), 'Service archived');
        } catch (\Throwable $e) {
            return $this->error('Failed to archive: ' . $e->getMessage());
        }
    }

    public function unarchive(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->unarchive($service);

            return $this->success(new ServiceResource($service), 'Service unarchived');
        } catch (\Throwable $e) {
            return $this->error('Failed to unarchive: ' . $e->getMessage());
        }
    }

    public function feature(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->feature($service);

            return $this->success(new ServiceResource($service), 'Service featured');
        } catch (\Throwable $e) {
            return $this->error('Failed to feature: ' . $e->getMessage());
        }
    }

    public function unfeature(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->unfeature($service);

            return $this->success(new ServiceResource($service), 'Service unfeatured');
        } catch (\Throwable $e) {
            return $this->error('Failed to unfeature: ' . $e->getMessage());
        }
    }

    public function clone(CloneServiceRequest $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $cloned = $this->commandService->clone($service, $request->validated()['new_slug']);

            return $this->created(new ServiceResource($cloned), 'Service cloned');
        } catch (\Throwable $e) {
            return $this->error('Failed to clone: ' . $e->getMessage());
        }
    }

    public function reorder(ReorderServicesRequest $request): JsonResponse
    {
        try {
            $this->commandService->reorder($request->validated()['order']);

            return $this->success(null, 'Services reordered');
        } catch (\Throwable $e) {
            return $this->error('Failed to reorder: ' . $e->getMessage());
        }
    }

    public function createTranslation(CreateServiceTranslationRequest $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $data = $request->validated();
            $service = $this->commandService->createTranslation($service, $data['locale'], array_diff_key($data, ['locale' => '']));

            return $this->success(new ServiceResource($service), 'Translation created');
        } catch (\Throwable $e) {
            return $this->error('Failed to create translation: ' . $e->getMessage());
        }
    }

    public function attachMedia(MediaIdsRequest $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->attachMedia($service, $request->validated()['media_ids']);

            return $this->success(new ServiceResource($service), 'Media attached');
        } catch (\Throwable $e) {
            return $this->error('Failed to attach media: ' . $e->getMessage());
        }
    }

    public function detachMedia(MediaIdsRequest $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->detachMedia($service, $request->validated()['media_ids']);

            return $this->success(new ServiceResource($service), 'Media detached');
        } catch (\Throwable $e) {
            return $this->error('Failed to detach media: ' . $e->getMessage());
        }
    }

    public function reorderMedia(ReorderServicesRequest $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->reorderMedia($service, $request->validated()['order']);

            return $this->success(new ServiceResource($service), 'Media reordered');
        } catch (\Throwable $e) {
            return $this->error('Failed to reorder media: ' . $e->getMessage());
        }
    }

    public function syncCategories(SyncCategoriesRequest $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->syncCategories($service, $request->validated()['term_ids']);

            return $this->success(new ServiceResource($service), 'Categories synced');
        } catch (\Throwable $e) {
            return $this->error('Failed to sync categories: ' . $e->getMessage());
        }
    }

    public function attachRelated(AttachRelatedRequest $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->attachRelated($service, $request->validated()['service_ids']);

            return $this->success(new ServiceResource($service), 'Related services attached');
        } catch (\Throwable $e) {
            return $this->error('Failed to attach related: ' . $e->getMessage());
        }
    }

    public function restoreRevision(RestoreRevisionRequest $request, string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $service = $this->commandService->restoreRevision($service, $request->validated()['revision_id']);

            return $this->success(new ServiceResource($service), 'Revision restored');
        } catch (\Throwable $e) {
            return $this->error('Failed to restore revision: ' . $e->getMessage());
        }
    }

    public function indexInSearch(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $this->commandService->indexInSearch($service);

            return $this->success(null, 'Service indexed');
        } catch (\Throwable $e) {
            return $this->error('Failed to index: ' . $e->getMessage());
        }
    }

    public function removeFromIndex(string $id): JsonResponse
    {
        try {
            $service = $this->queryService->find($id);

            if (!$service) {
                return $this->notFound('Service not found');
            }

            $this->commandService->removeFromIndex($service);

            return $this->success(null, 'Service removed from index');
        } catch (\Throwable $e) {
            return $this->error('Failed to remove from index: ' . $e->getMessage());
        }
    }
}
