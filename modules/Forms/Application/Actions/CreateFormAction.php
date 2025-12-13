<?php

declare(strict_types=1);

namespace Modules\Forms\Application\Actions;

use Illuminate\Support\Str;
use Modules\Core\Application\Actions\Action;
use Modules\Forms\Domain\DTO\FormData;
use Modules\Forms\Domain\Models\Form;
use Modules\Forms\Domain\Repositories\FormRepository;

/**
 * Create Form Action.
 */
final class CreateFormAction extends Action
{
    public function __construct(
        private readonly FormRepository $repository
    ) {}

    public function execute(FormData $data): Form
    {
        return $this->transaction(function () use ($data) {
            $form = $this->repository->create([
                'slug' => $data->slug ?? Str::slug($data->name),
                'name' => $data->name,
                'description' => $data->description,
                'type' => $data->type,
                'success_message' => json_encode($data->success_message),
                'notification_emails' => $data->notification_emails,
                'redirect_url' => $data->redirect_url,
                'is_active' => $data->is_active,
                'captcha_enabled' => $data->captcha_enabled,
                'settings' => $data->settings,
            ]);

            $this->createFields($form, $data->fields);

            return $form->fresh(['fields']);
        });
    }

    private function createFields(Form $form, array $fields): void
    {
        foreach ($fields as $index => $field) {
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
}
