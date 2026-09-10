<?php

namespace App\Nexus\Modules\PaymentMethod\Jobs;

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Services\Payments\PaymentManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckInstallmentPaymentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function handle(PaymentManager $payments): void
    {
        if ($this->order->payment_status !== PaymentStatus::PENDING) {
            return;
        }

        $driver = $payments->driver($this->order->payment_method);
        $driver->checkPayment($this->order);
    }
}
