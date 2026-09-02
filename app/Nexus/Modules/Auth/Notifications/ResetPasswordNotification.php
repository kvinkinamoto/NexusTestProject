<?php

namespace App\Nexus\Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $token,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->buildUrl($notifiable);

        return (new MailMessage)
            ->subject(__('auth::translate.password_forgot.subject'))
            ->view('auth::mail.reset-password', [
                'user' => $notifiable,
                'url' => $url,
            ]);
    }

    protected function buildUrl(object $notifiable): string
    {
        $email = $notifiable->getEmailForPasswordReset();
        $frontendUrl = config('auth.password_reset_url');

        if ($frontendUrl) {
            return $frontendUrl . '?' . http_build_query([
                'token' => $this->token,
                'email' => $email,
            ]);
        }

        return url(route('password.reset', [
            'token' => $this->token,
            'email' => $email,
        ], false));
    }
}
