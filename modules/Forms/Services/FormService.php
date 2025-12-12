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

class FormService implements FormServiceContract
{
    public function __construct(
        protected SpamDetector $spamDetector,
        protected FormValidator $formValidator
    ) {}

    public function all(): Collection
    {
        return Form::with('fields')->get();
    }

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

    public function find(string $id): ?Form
    {
        return Form::with('fields')->find($id);
    }

    public function findBySlug(string $slug): ?Form
    {
        return Form::with('fields')->where('slug', $slug)->active()->first();
    }

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

    public function delete(Form $form): bool
    {
        return $form->delete();
    }

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

    public function updateSubmissionStatus(FormSubmission $submission, string $status): FormSubmission
    {
        $submission->update(['status' => $status]);

        if ($status === 'processed') {
            $submission->update(['processed_at' => now()]);
        }

        return $submission->fresh();
    }
}
