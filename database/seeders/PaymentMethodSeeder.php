<?php

namespace Database\Seeders;

use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use Illuminate\Database\Seeder;

/**
 * Nexus Store sells only digital licenses, so LiqPay (an online card
 * gateway) is the sole payment method — no cash-on-delivery/offline rows.
 * checkout.blade.php only renders the payment section (and the redirect to
 * /payment/{orderId}) when `type` is exactly 'gateway'; `code` must match
 * PaymentManager::driver()'s 'liqpay' case.
 */
class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        PaymentMethod::query()->updateOrCreate(
            ['code' => 'liqpay'],
            [
                'provider' => 'liqpay',
                'type' => 'gateway',
                'title' => 'Оплата карткою (LiqPay)',
                'description' => 'Visa/Mastercard через LiqPay. Посилання на завантаження та ліцензійний ключ надійдуть на email одразу після оплати.',
                'publish' => true,
            ],
        );
    }
}
