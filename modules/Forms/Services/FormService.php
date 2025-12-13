<?php

declare(strict_types=1);

namespace Modules\Forms\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\Forms\Contracts\FormServiceContract;
use Modules\Forms\Domain\Models\Form;
use Modules\Forms\Domain\Models\FormSubmission;
use Modules\Forms\Jobs\ProcessSubmission;

/**
 * Class FormService
 *
 * Service class for managing dynamic forms including
 * CRUD, submissions, and spam detection.
 *
 * @package Modules\Forms\Services
 */
class FormService implements FormServiceContract
{
    /**
     * The spam detector instance.
     *
     * @var SpamDetector
     */
    protected SpamDetector $spamDetector;

    /**
     * The form validator instance.
     *
     * @var FormValidator
     */
    protected FormValidator $formValidator;

    /**
     * Create a new FormService instance.
     *
     * @param SpamDetector $spamDetector The spam detector
     * @param FormValidator $formValidator The form validator
     */
    public function __construct(
        SpamDetector $spamDetector,
        FormValidator $formValidator
    ) {
        $this->spamDetector = $spamDetector;
        $this->formValidator = $formValidator;
    }

    /**
     * Get all forms with their fields.
     *
     * @return Collection Collection of Form models
     */
    public function all(): Collection
    {
        return Form::with('fields')->get();
    }

    /**
     * Get a paginated list of forms with optional filtering.
     *
     * @param array $filters Optional filters: 'type', 'active', 'search'
     * @param int $perPage Results per page
     *
     * @return LengthAwarePaginator Paginated forms
     */
    public function list(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Form::withCount('submissions');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['active'])) {
            $query->where('is_active', $filters['active']);
        }

        if (!empty($filters['search'])) {
            $query->where('name', 'LIKE', "%{$filters['search']}%");
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Find a form by its UUID.
     *
     * @param string $id The form UUID
     *
     * @return Form|null The found form or null
     */
    public function find(string $id): ?Form
    {
        return Form::with('fields')->find($id);
    }

    /**
     * Find an active form by its slug.
     *
     * @param string $slug The form slug
     *
     * @return Form|null The found form or null
     */
    public function findBySlug(string $slug): ?Form
    {
        return Form::with('fields')->where('slug', $slug)->active()->first();
    }

    /**
     * Create a new form with fields.
     *
     * @param array $data Form data including fields
     *
     * @return Form The created form
     *
     * @throws \Throwable If transaction fails
     */
    public function create(array $data): Form
    {
        return DB::transaction(function () use ($data) {
            $form = Form::create([
                'slug' => $data['slug'] ?? Str::slug($data['name']),
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'type' => $data['type'] ?? 'contact',
                'success_message' => json_encode($data['success_message'] ?? ['en' => 'Thank you!']),
                'notification_emails' => $data['notification_emails'] ?? [],
                'redirect_url' => $data['redirect_url'] ?? null,
                'is_active' => $data['is_active'] ?? true,
                'captcha_enabled' => $data['captcha_enabled'] ?? false,
                'settings' => $data['settings'] ?? [],
            ]);

            if (!empty($data['fields'])) {
                foreach ($data['fields'] as $index => $field) {
                    $form->fields()->create([
                        'name' => $field['name'],
                        'label' => json_encode($field['label'] ?? ['en' => $field['name']]),
                        'type' => $field['type'] ?? 'text',
                        'placeholder' => isset($field['placeholder']) ? json_encode($field['placeholder']) : null,
                        'default_value' => $field['default_value'] ?? null,
                        'options' => $field['options'] ?? null,
                        'validation_rules' => $field['validation_rules'] ?? [],
                        'is_required' => $field['is_required'] ?? false,
                        'ordering' => $field['ordering'] ?? $index + 1,
                        'conditions' => $field['conditions'] ?? null,
                    ]);
                }
            }

            return $form->fresh(['fields']);
        });
    }

    /**
     * Update an existing form and its fields.
     *
     * @param Form $form The form to update
     * @param array $data Updated data
     *
     * @return Form The updated form
     *
     * @throws \Throwable If transaction fails
     */
    public function update(Form $form, array $data): Form
    {
        return DB::transaction(function () use ($form, $data) {
            $form->update([
                'name' => $data['name'] ?? $form->name,
                'description' => $data['description'] ?? $form->description,
                'type' => $data['type'] ?? $form->type,
                'success_message' => isset($data['success_message']) ? json_encode($data['success_message']) : $form->getRawOriginal('success_message'),
                'notification_emails' => $data['notification_emails'] ?? $form->notification_emails,
                'redirect_url' => $data['redirect_url'] ?? $form->redirect_url,
                'is_active' => $data['is_active'] ?? $form->is_active,
                'captcha_enabled' => $data['captcha_enabled'] ?? $form->captcha_enabled,
                'settings' => array_merge($form->settings ?? [], $data['settings'] ?? []),
            ]);

            if (isset($data['fields'])) {
                $form->fields()->delete();
                foreach ($data['fields'] as $index => $field) {
                    $form->fields()->create([
                        'name' => $field['name'],
                        'label' => json_encode($field['label'] ?? ['en' => $field['name']]),
                        'type' => $field['type'] ?? 'text',
                        'placeholder' => isset($field['placeholder']) ? json_encode($field['placeholder']) : null,
                        'default_value' => $field['default_value'] ?? null,
                        'options' => $field['options'] ?? null,
                        'validation_rules' => $field['validation_rules'] ?? [],
                        'is_required' => $field['is_required'] ?? false,
                        'ordering' => $field['ordering'] ?? $index + 1,
                        'conditions' => $field['conditions'] ?? null,
                    ]);
                }
            }

            return $form->fresh(['fields']);
        });
    }

    /**
     * Delete a form.
     *
     * @param Form $form The form to delete
     *
     * @return bool True if successful
     */
    public function delete(Form $form): bool
    {
        return $form->delete();
    }

    /**
     * Submit data to a form.
     *
     * @param Form $form The form being submitted
     * @param array $data Submitted form data
     * @param array $meta Additional metadata (ip, user_agent, referrer)
     *
     * @return FormSubmission The created submission
     *
     * @throws \Illuminate\Validation\ValidationException If validation fails
     */
    public function submit(Form $form, array $data, array $meta = []): FormSubmission
    {
        $this->formValidator->validate($form, $data);

        $status = 'new';
        if ($this->spamDetector->isSpam($data, $meta)) {
            $status = 'spam';
        }

        $submission = $form->submissions()->create([
            'user_id' => auth()->id(),
            'data' => $data,
            'ip_address' => $meta['ip'] ?? request()->ip(),
            'user_agent' => $meta['user_agent'] ?? request()->userAgent(),
            'referrer' => $meta['referrer'] ?? request()->header('referer'),
            'status' => $status,
        ]);

        if ($status !== 'spam' && config('forms.notifications.enabled')) {
            dispatch(new ProcessSubmission($submission));
        }

        return $submission;
    }

    /**
     * Get paginated submissions for a form.
     *
     * @param string $formId The form UUID
     * @param array $filters Optional filters: 'status', 'exclude_spam'
     * @param int $perPage Results per page
     *
     * @return LengthAwarePaginator Paginated submissions
     */
    public function getSubmissions(string $formId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = FormSubmission::where('form_id', $formId)->with('user');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['exclude_spam']) && $filters['exclude_spam']) {
            $query->notSpam();
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Update a submission's status.
     *
     * @param FormSubmission $submission The submission to update
     * @param string $status New status
     *
     * @return FormSubmission The updated submission
     */
    public function updateSubmissionStatus(FormSubmission $submission, string $status): FormSubmission
    {
        $submission->update(['status' => $status]);

        if ($status === 'processed') {
            $submission->update(['processed_at' => now()]);
        }

        return $submission->fresh();
    }
}
