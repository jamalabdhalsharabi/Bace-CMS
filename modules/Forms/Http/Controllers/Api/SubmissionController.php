<?php

declare(strict_types=1);

namespace Modules\Forms\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Forms\Application\Services\FormCommandService;
use Modules\Forms\Application\Services\FormQueryService;
use Modules\Forms\Http\Requests\UpdateSubmissionStatusRequest;
use Modules\Forms\Http\Resources\FormSubmissionResource;

/**
 * Submission API Controller.
 *
 * Follows Clean Architecture principles.
 */
class SubmissionController extends BaseController
{
    public function __construct(
        protected FormQueryService $queryService,
        protected FormCommandService $commandService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $submissions = $this->queryService->getAllSubmissions(
            $request->integer('per_page', 20),
            $request->only(['form_id', 'status'])
        );
        return $this->paginated(FormSubmissionResource::collection($submissions)->resource);
    }

    public function show(string $id): JsonResponse
    {
        $submission = $this->queryService->findSubmission($id);
        return $submission 
            ? $this->success(new FormSubmissionResource($submission)) 
            : $this->notFound('Submission not found');
    }

    public function updateStatus(UpdateSubmissionStatusRequest $request, string $id): JsonResponse
    {
        $submission = $this->commandService->updateSubmissionStatus($id, $request->validated()['status']);
        return $this->success(new FormSubmissionResource($submission));
    }

    public function destroy(string $id): JsonResponse
    {
        $this->commandService->deleteSubmission($id);
        return $this->success(null, 'Submission deleted');
    }
}
