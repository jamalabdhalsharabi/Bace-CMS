<?php

declare(strict_types=1);

namespace Modules\Forms\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Modules\Forms\Domain\Models\FormSubmission;

class ProcessSubmission implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(
        public FormSubmission $submission
    ) {
        $this->queue = config('forms.notifications.queue', 'default');
    }

    public function handle(): void
    {
        $form = $this->submission->form;

        if (empty($form->notification_emails)) {
            return;
        }

        $this->sendNotificationEmails($form->notification_emails);
    }

    protected function sendNotificationEmails(array $emails): void
    {
        $form = $this->submission->form;
        $data = $this->submission->data;

        foreach ($emails as $email) {
            Mail::raw($this->buildEmailContent($form->name, $data), function ($message) use ($email, $form) {
                $message->to($email)
                    ->subject("New submission: {$form->name}");
            });
        }
    }

    protected function buildEmailContent(string $formName, array $data): string
    {
        $content = "New submission received for: {$formName}\n\n";

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $content .= ucfirst(str_replace('_', ' ', $key)) . ": {$value}\n";
        }

        $content .= "\nSubmitted at: " . $this->submission->created_at->format('Y-m-d H:i:s');

        return $content;
    }
}
