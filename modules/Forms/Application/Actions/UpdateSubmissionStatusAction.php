<?php

declare(strict_types=1);

namespace Modules\Forms\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Forms\Domain\Models\FormSubmission;

/**
 * Update Submission Status Action.
 *
 * Updates the status of a form submission.
 * Automatically sets processed_at timestamp when status is 'processed'.
 *
 * @package Modules\Forms\Application\Actions
 * @author  CMS Development Team
 * @since   1.0.0
 */
final class UpdateSubmissionStatusAction extends Action
{
    /**
     * Execute the update status action.
     *
     * @param FormSubmission $submission The submission to update
     * @param string $status The new status value
     *
     * @return FormSubmission The updated submission
     */
    public function execute(FormSubmission $submission, string $status): FormSubmission
    {
        $data = ['status' => $status];

        if ($status === 'processed') {
            $data['processed_at'] = now();
        }

        $submission->update($data);

        return $submission->fresh();
    }
}
