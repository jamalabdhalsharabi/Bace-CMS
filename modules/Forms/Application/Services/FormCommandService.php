<?php

declare(strict_types=1);

namespace Modules\Forms\Application\Services;

use Modules\Forms\Application\Actions\CreateFormAction;
use Modules\Forms\Application\Actions\DeleteFormAction;
use Modules\Forms\Application\Actions\DuplicateFormAction;
use Modules\Forms\Application\Actions\SubmitFormAction;
use Modules\Forms\Application\Actions\ToggleFormAction;
use Modules\Forms\Application\Actions\UpdateFormAction;
use Modules\Forms\Application\Actions\UpdateSubmissionStatusAction;
use Modules\Forms\Domain\DTO\FormData;
use Modules\Forms\Domain\DTO\SubmissionData;
use Modules\Forms\Domain\Models\Form;
use Modules\Forms\Domain\Models\FormSubmission;

/**
 * Form Command Service.
 *
 * Orchestrates all write operations for forms via Action classes.
 * No direct Model usage - delegates all mutations to dedicated Actions.
 *
 * @package Modules\Forms\Application\Services
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class FormCommandService
{
    /**
     * Create a new FormCommandService instance.
     *
     * @param CreateFormAction $createAction Action for creating forms
     * @param UpdateFormAction $updateAction Action for updating forms
     * @param DeleteFormAction $deleteAction Action for deleting forms
     * @param DuplicateFormAction $duplicateAction Action for duplicating forms
     * @param ToggleFormAction $toggleAction Action for toggling form status
     * @param SubmitFormAction $submitAction Action for handling form submissions
     * @param UpdateSubmissionStatusAction $updateStatusAction Action for updating submission status
     */
    public function __construct(
        private readonly CreateFormAction $createAction,
        private readonly UpdateFormAction $updateAction,
        private readonly DeleteFormAction $deleteAction,
        private readonly DuplicateFormAction $duplicateAction,
        private readonly ToggleFormAction $toggleAction,
        private readonly SubmitFormAction $submitAction,
        private readonly UpdateSubmissionStatusAction $updateStatusAction,
    ) {}

    /**
     * Create a new form.
     *
     * @param FormData $data The form data DTO
     *
     * @return Form The created form
     */
    public function create(FormData $data): Form
    {
        return $this->createAction->execute($data);
    }

    /**
     * Update an existing form.
     *
     * @param Form $form The form to update
     * @param FormData $data The updated form data
     *
     * @return Form The updated form
     */
    public function update(Form $form, FormData $data): Form
    {
        return $this->updateAction->execute($form, $data);
    }

    /**
     * Delete a form.
     *
     * @param Form $form The form to delete
     *
     * @return bool True if deletion was successful
     */
    public function delete(Form $form): bool
    {
        return $this->deleteAction->execute($form);
    }

    /**
     * Activate a form.
     *
     * @param Form $form The form to activate
     *
     * @return Form The activated form
     */
    public function activate(Form $form): Form
    {
        return $this->toggleAction->activate($form);
    }

    /**
     * Deactivate a form.
     *
     * @param Form $form The form to deactivate
     *
     * @return Form The deactivated form
     */
    public function deactivate(Form $form): Form
    {
        return $this->toggleAction->deactivate($form);
    }

    /**
     * Submit a form response.
     *
     * @param Form $form The form being submitted
     * @param SubmissionData $data The submission data DTO
     *
     * @return FormSubmission The created submission
     */
    public function submit(Form $form, SubmissionData $data): FormSubmission
    {
        return $this->submitAction->execute($form, $data);
    }

    /**
     * Duplicate a form.
     *
     * @param Form $form The form to duplicate
     *
     * @return Form The duplicated form
     */
    public function duplicate(Form $form): Form
    {
        return $this->duplicateAction->execute($form);
    }

    /**
     * Update submission status.
     *
     * @param FormSubmission $submission The submission to update
     * @param string $status The new status
     *
     * @return FormSubmission The updated submission
     */
    public function updateSubmissionStatus(FormSubmission $submission, string $status): FormSubmission
    {
        return $this->updateStatusAction->execute($submission, $status);
    }
}
