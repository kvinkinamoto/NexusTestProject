<?php

namespace App\Nexus\Modules\Auth\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class VerifyEmailNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = $this->buildUrl($notifiable);

        return (new MailMessage)
            ->subject(__('auth::translate.email_verification.subject'))
            ->view('auth::mail.verify-email', [
                'user' => $notifiable,
                'url' => $url,
            ]);
    }

    protected function buildUrl(object $notifiable): string
    {
        $expiresAt = Carbon::now()->addMinutes(config('auth.verification.expire', 60));
        $params = [
            'id' => $notifiable->getKey(),
            'hash' => sha1($notifiable->getEmailForVerification()),
        ];

        $frontendUrl = config('auth.email_verification_url');

        if ($frontendUrl) {
            $signedRoute = URL::temporarySignedRoute('verification.verify', $expiresAt, $params, false);
            $query = parse_url($signedRoute, PHP_URL_QUERY);

            return $frontendUrl . '?' . http_build_query($params) . '&' . $query;
        }

        return URL::temporarySignedRoute('verification.verify', $expiresAt, $params);
    }
}
