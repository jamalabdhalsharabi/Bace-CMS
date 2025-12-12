<?php

declare(strict_types=1);

namespace Modules\Forms\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Core\Http\Controllers\BaseController;
use Modules\Forms\Contracts\FormServiceContract;
use Modules\Forms\Http\Requests\CreateFormRequest;
use Modules\Forms\Http\Requests\SubmitFormRequest;
use Modules\Forms\Http\Requests\UpdateFormRequest;
use Modules\Forms\Http\Resources\FormResource;
use Modules\Forms\Http\Resources\FormSubmissionResource;

class FormController extends BaseController
{
    public function __construct(
        protected FormServiceContract $formService
    ) {}

    public function index(Request $request): JsonResponse
    {
        $forms = $this->formService->list(
            filters: $request->only(['type', 'active', 'search']),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginated(FormResource::collection($forms)->resource);
    }

    public function show(string $id): JsonResponse
    {
        $form = $this->formService->find($id);

        if (!$form) {
            return $this->notFound('Form not found');
        }

        return $this->success(new FormResource($form));
    }

    public function showBySlug(string $slug): JsonResponse
    {
        $form = $this->formService->findBySlug($slug);

        if (!$form) {
            return $this->notFound('Form not found');
        }

        return $this->success(new FormResource($form));
    }

    public function store(CreateFormRequest $request): JsonResponse
    {
        $form = $this->formService->create($request->validated());

        return $this->created(new FormResource($form), 'Form created successfully');
    }

    public function update(UpdateFormRequest $request, string $id): JsonResponse
    {
        $form = $this->formService->find($id);

        if (!$form) {
            return $this->notFound('Form not found');
        }

        $form = $this->formService->update($form, $request->validated());

        return $this->success(new FormResource($form), 'Form updated successfully');
    }

    public function destroy(string $id): JsonResponse
    {
        $form = $this->formService->find($id);

        if (!$form) {
            return $this->notFound('Form not found');
        }

        $this->formService->delete($form);

        return $this->success(null, 'Form deleted successfully');
    }

    public function submit(SubmitFormRequest $request, string $slug): JsonResponse
    {
        $form = $this->formService->findBySlug($slug);

        if (!$form) {
            return $this->notFound('Form not found');
        }

        try {
            $submission = $this->formService->submit($form, $request->validated(), [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'referrer' => $request->header('referer'),
            ]);

            return $this->created([
                'message' => $form->success_message,
                'redirect_url' => $form->redirect_url,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->error('Validation failed', 422, $e->errors());
        }
    }

    public function submissions(Request $request, string $id): JsonResponse
    {
        $submissions = $this->formService->getSubmissions(
            $id,
            filters: $request->only(['status', 'exclude_spam']),
            perPage: $request->integer('per_page', 20)
        );

        return $this->paginated(FormSubmissionResource::collection($submissions)->resource);
    }

    public function updateSubmissionStatus(Request $request, string $submissionId): JsonResponse
    {
        $request->validate(['status' => 'required|in:new,read,spam,processed']);

        $submission = \Modules\Forms\Domain\Models\FormSubmission::find($submissionId);

        if (!$submission) {
            return $this->notFound('Submission not found');
        }

        $submission = $this->formService->updateSubmissionStatus($submission, $request->status);

        return $this->success(new FormSubmissionResource($submission));
    }
}
