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

/**
 * Class FormController
 *
 * API controller for managing dynamic forms, form submissions,
 * and submission status management.
 *
 * @package Modules\Forms\Http\Controllers\Api
 */
class FormController extends BaseController
{
    /**
     * The form service instance.
     *
     * @var FormServiceContract
     */
    protected FormServiceContract $formService;

    /**
     * Create a new FormController instance.
     *
     * @param FormServiceContract $formService The form service implementation
     */
    public function __construct(
        FormServiceContract $formService
    ) {
        $this->formService = $formService;
    }

    /**
     * Display a paginated listing of forms.
     *
     * @param Request $request The request with optional filters
     * @return JsonResponse Paginated list of forms
     */
    public function index(Request $request): JsonResponse
    {
        $forms = $this->formService->list(
            filters: $request->only(['type', 'active', 'search']),
            perPage: $request->integer('per_page', 15)
        );

        return $this->paginated(FormResource::collection($forms)->resource);
    }

    /**
     * Display the specified form by its UUID.
     *
     * @param string $id The UUID of the form
     * @return JsonResponse The form data or 404 error
     */
    public function show(string $id): JsonResponse
    {
        $form = $this->formService->find($id);

        if (!$form) {
            return $this->notFound('Form not found');
        }

        return $this->success(new FormResource($form));
    }

    /**
     * Display the specified form by its slug.
     *
     * @param string $slug The URL-friendly slug
     * @return JsonResponse The form data or 404 error
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $form = $this->formService->findBySlug($slug);

        if (!$form) {
            return $this->notFound('Form not found');
        }

        return $this->success(new FormResource($form));
    }

    /**
     * Store a newly created form.
     *
     * @param CreateFormRequest $request The validated form data
     * @return JsonResponse The created form (HTTP 201)
     */
    public function store(CreateFormRequest $request): JsonResponse
    {
        $form = $this->formService->create($request->validated());

        return $this->created(new FormResource($form), 'Form created successfully');
    }

    /**
     * Update the specified form.
     *
     * @param UpdateFormRequest $request The validated form data
     * @param string $id The UUID of the form
     * @return JsonResponse The updated form or 404 error
     */
    public function update(UpdateFormRequest $request, string $id): JsonResponse
    {
        $form = $this->formService->find($id);

        if (!$form) {
            return $this->notFound('Form not found');
        }

        $form = $this->formService->update($form, $request->validated());

        return $this->success(new FormResource($form), 'Form updated successfully');
    }

    /**
     * Delete the specified form.
     *
     * @param string $id The UUID of the form
     * @return JsonResponse Success message or 404 error
     */
    public function destroy(string $id): JsonResponse
    {
        $form = $this->formService->find($id);

        if (!$form) {
            return $this->notFound('Form not found');
        }

        $this->formService->delete($form);

        return $this->success(null, 'Form deleted successfully');
    }

    /**
     * Submit a form response.
     *
     * @param SubmitFormRequest $request The form submission data
     * @param string $slug The form slug
     * @return JsonResponse Success message with redirect URL or validation error
     * @throws \Illuminate\Validation\ValidationException If validation fails
     */
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

    /**
     * Get paginated submissions for a form.
     *
     * @param Request $request The request with optional filters
     * @param string $id The UUID of the form
     * @return JsonResponse Paginated list of submissions
     */
    public function submissions(Request $request, string $id): JsonResponse
    {
        $submissions = $this->formService->getSubmissions(
            $id,
            filters: $request->only(['status', 'exclude_spam']),
            perPage: $request->integer('per_page', 20)
        );

        return $this->paginated(FormSubmissionResource::collection($submissions)->resource);
    }

    /**
     * Update the status of a form submission.
     *
     * @param Request $request The request containing new status
     * @param string $submissionId The UUID of the submission
     * @return JsonResponse The updated submission or 404 error
     */
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
