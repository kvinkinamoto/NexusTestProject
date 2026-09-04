<?php

namespace App\Nexus\Modules\Form\Notifications;

use App\Nexus\Modules\Form\Models\Form;
use App\Nexus\Modules\FormSubmission\Models\FormSubmission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class FormSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Form $form,
        public FormSubmission $submission,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject(__('form::translate.submission.mail_subject', ['title' => $this->form->title]))
            ->greeting(__('form::translate.submission.mail_greeting', ['title' => $this->form->title]));

        foreach ((array) $this->submission->data as $key => $value) {
            $message->line(sprintf('%s: %s', $key, is_array($value) ? implode(', ', $value) : (string) $value));
        }

        return $message;
    }
}
