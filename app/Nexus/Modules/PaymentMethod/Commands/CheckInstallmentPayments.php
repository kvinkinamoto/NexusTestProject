<?php

namespace App\Nexus\Modules\PaymentMethod\Commands;

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Jobs\CheckInstallmentPaymentJob;
use Illuminate\Console\Command;

class CheckInstallmentPayments extends Command
{
    protected $signature = 'payments:check-installments';

    protected $description = 'Poll the payment gateway for pending installment orders created in the last 25 minutes.';

    public function handle(): void
    {
        Order::query()
            ->where('payment_status', PaymentStatus::PENDING)
            ->whereIn('payment_method', ['privat_installment', 'mono_installment', 'liqpay'])
            ->whereNotNull('bank_order_id')
            ->where('created_at', '>=', now()->subMinutes(25))
            ->chunkById(50, function ($orders) {
                foreach ($orders as $order) {
                    CheckInstallmentPaymentJob::dispatch($order)->delay(now()->addSeconds(rand(1, 10)));
                }
            });
    }
}
