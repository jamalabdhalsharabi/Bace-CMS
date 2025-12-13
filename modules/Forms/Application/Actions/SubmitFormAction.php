<?php

declare(strict_types=1);

namespace Modules\Forms\Application\Actions;

use Modules\Core\Application\Actions\Action;
use Modules\Forms\Domain\DTO\SubmissionData;
use Modules\Forms\Domain\Events\FormSubmitted;
use Modules\Forms\Domain\Models\Form;
use Modules\Forms\Domain\Models\FormSubmission;
use Modules\Forms\Jobs\ProcessSubmission;
use Modules\Forms\Services\SpamDetector;

/**
 * Submit Form Action.
 */
final class SubmitFormAction extends Action
{
    public function __construct(
        private readonly SpamDetector $spamDetector
    ) {}

    public function execute(Form $form, SubmissionData $data): FormSubmission
    {
        $meta = [
            'ip' => $data->ip_address ?? request()->ip(),
            'user_agent' => $data->user_agent ?? request()->userAgent(),
        ];

        $status = 'new';
        if ($this->spamDetector->isSpam($data->data, $meta)) {
            $status = 'spam';
        }

        $submission = $form->submissions()->create([
            'user_id' => $data->user_id ?? $this->userId(),
            'data' => $data->data,
            'ip_address' => $meta['ip'],
            'user_agent' => $meta['user_agent'],
            'referrer' => $data->referrer ?? request()->header('referer'),
            'status' => $status,
        ]);

        if ($status !== 'spam' && config('forms.notifications.enabled')) {
            dispatch(new ProcessSubmission($submission));
        }

        event(new FormSubmitted($submission));

        return $submission;
    }
}
