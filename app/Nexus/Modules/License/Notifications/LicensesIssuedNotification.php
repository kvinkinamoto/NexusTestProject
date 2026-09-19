<?php

namespace App\Nexus\Modules\License\Notifications;

use App\Nexus\Modules\Order\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LicensesIssuedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly Order $order,
        private readonly Collection $licenses,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject("Замовлення #{$this->order->id} оплачено — ваші ліцензії готові")
            ->view('license::mail.licenses-issued', [
                'user' => $notifiable,
                'order' => $this->order,
                'licenses' => $this->licenses->loadMissing('product'),
                'url' => route('account.licenses'),
            ]);
    }
}
