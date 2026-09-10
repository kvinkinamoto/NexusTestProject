<?php

use App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers\LiqpayDriver;
use App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers\MonobankInstallmentDriver;
use App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers\PrivatInstallmentDriver;
use App\Nexus\Modules\PaymentMethod\Services\Payments\PaymentManager;

beforeEach(function () {
    $this->manager = app(PaymentManager::class);
});

it('resolves the liqpay driver', function () {
    expect($this->manager->driver('liqpay'))->toBeInstanceOf(LiqpayDriver::class);
});

it('resolves the monobank installment driver', function () {
    expect($this->manager->driver('mono_installment'))->toBeInstanceOf(MonobankInstallmentDriver::class);
});

it('resolves the privatbank installment driver', function () {
    expect($this->manager->driver('privat_installment'))->toBeInstanceOf(PrivatInstallmentDriver::class);
});

it('throws for an offline or unknown payment method code', function () {
    $this->manager->driver('cash_on_delivery');
})->throws(UnhandledMatchError::class);
