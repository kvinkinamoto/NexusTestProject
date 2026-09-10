<?php

use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use App\Nexus\Modules\PaymentMethod\Services\PaymentMethodService;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'PaymentMethod'])->run();

    $this->service = app(PaymentMethodService::class);
});

it('groups published payment methods by type', function () {
    PaymentMethod::create(['code' => 'cash', 'provider' => 'offline', 'type' => 'offline', 'title' => 'Cash', 'publish' => true]);
    PaymentMethod::create(['code' => 'liqpay', 'provider' => 'liqpay', 'type' => 'gateway', 'title' => 'Online', 'publish' => true]);
    PaymentMethod::create(['code' => 'mono_installment', 'provider' => 'monobank', 'type' => 'installment', 'title' => 'Parts', 'publish' => true, 'count_installment' => 3]);

    $grouped = $this->service->getPaymentMethods();

    expect($grouped->keys()->sort()->values()->all())->toBe(['gateway', 'installment', 'offline']);
    expect($grouped['gateway']->first()->code)->toBe('liqpay');
});

it('excludes unpublished payment methods', function () {
    PaymentMethod::create(['code' => 'cash', 'provider' => 'offline', 'type' => 'offline', 'title' => 'Cash', 'publish' => false]);

    expect($this->service->getPaymentMethods())->toBeEmpty();
});
