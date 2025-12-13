<?php

declare(strict_types=1);

namespace Modules\Forms\Application\Services;

use Modules\Forms\Application\Actions\CreateFormAction;
use Modules\Forms\Application\Actions\DeleteFormAction;
use Modules\Forms\Application\Actions\DuplicateFormAction;
use Modules\Forms\Application\Actions\SubmitFormAction;
use Modules\Forms\Application\Actions\ToggleFormAction;
use Modules\Forms\Application\Actions\UpdateFormAction;
use Modules\Forms\Domain\DTO\FormData;
use Modules\Forms\Domain\DTO\SubmissionData;
use Modules\Forms\Domain\Models\Form;
use Modules\Forms\Domain\Models\FormSubmission;

/**
 * Form Command Service.
 */
final class FormCommandService
{
    public function __construct(
        private readonly CreateFormAction $createAction,
        private readonly UpdateFormAction $updateAction,
        private readonly DeleteFormAction $deleteAction,
        private readonly DuplicateFormAction $duplicateAction,
        private readonly ToggleFormAction $toggleAction,
        private readonly SubmitFormAction $submitAction,
    ) {}

    public function create(FormData $data): Form
    {
        return $this->createAction->execute($data);
    }

    public function update(Form $form, FormData $data): Form
    {
        return $this->updateAction->execute($form, $data);
    }

    public function delete(Form $form): bool
    {
        return $this->deleteAction->execute($form);
    }

    public function activate(Form $form): Form
    {
        return $this->toggleAction->activate($form);
    }

    public function deactivate(Form $form): Form
    {
        return $this->toggleAction->deactivate($form);
    }

    public function submit(Form $form, SubmissionData $data): FormSubmission
    {
        return $this->submitAction->execute($form, $data);
    }

    public function duplicate(Form $form): Form
    {
        return $this->duplicateAction->execute($form);
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
