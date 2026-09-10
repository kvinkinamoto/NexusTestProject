<?php

namespace App\Nexus\Modules\PaymentMethod\Services\Payments;

use App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers\LiqpayDriver;
use App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers\MonobankInstallmentDriver;
use App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers\PrivatInstallmentDriver;

class PaymentManager
{
    public function driver(string $code): PaymentDriverInterface
    {
        return match ($code) {
            'liqpay' => app(LiqpayDriver::class),
            'mono_installment' => app(MonobankInstallmentDriver::class),
            'privat_installment' => app(PrivatInstallmentDriver::class),
        };
    }
}
