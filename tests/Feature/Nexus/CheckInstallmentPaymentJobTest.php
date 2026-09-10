<?php

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Jobs\CheckInstallmentPaymentJob;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use App\Nexus\Modules\PaymentMethod\Services\Payments\PaymentManager;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'PaymentMethod'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    PaymentMethod::create(['code' => 'mono_installment', 'provider' => 'monobank', 'type' => 'installment', 'title' => 'Parts', 'publish' => true, 'count_installment' => 3]);
});

it('checks payment status for a pending order', function () {
    Http::fake(['*/api/order/state' => Http::response(['state' => 'FAIL'], 200)]);

    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'mono_installment', 'total_price' => 300, 'bank_order_id' => 'mono-abc', 'payment_status' => PaymentStatus::PENDING]);

    (new CheckInstallmentPaymentJob($order))->handle(app(PaymentManager::class));

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::CANCELLED);
});

it('skips orders that are no longer pending', function () {
    Http::fake();

    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'mono_installment', 'total_price' => 300, 'bank_order_id' => 'mono-abc', 'payment_status' => PaymentStatus::PAID]);

    (new CheckInstallmentPaymentJob($order))->handle(app(PaymentManager::class));

    Http::assertNothingSent();
});
