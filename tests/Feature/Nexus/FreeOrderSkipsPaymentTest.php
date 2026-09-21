<?php

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;

/**
 * A free order (every line free, or discounted to 0) is marked PAID the
 * moment OrderService creates it — see OrderService::markPaidIfFree() — so
 * it must never be reachable through the gateway "pay" route, whether the
 * checkout JS is bypassed or the buyer revisits a stale /payment/{id} link.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'PaymentMethod'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();
});

it('redirects a free (already-paid) order to the thank-you page instead of invoking a payment driver', function () {
    PaymentMethod::create(['code' => 'liqpay', 'provider' => 'liqpay', 'type' => 'gateway', 'title' => 'LiqPay', 'publish' => true]);
    $order = Order::create([
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'total_price' => 0, 'payment_method' => 'liqpay', 'payment_status' => PaymentStatus::PAID,
    ]);

    $this->get("/payment/{$order->id}")->assertRedirect(route('shop.checkout.thankyou', ['order' => $order->id]));
});

it('still invokes the payment driver for an order that genuinely needs to be paid', function () {
    PaymentMethod::create(['code' => 'liqpay', 'provider' => 'liqpay', 'type' => 'gateway', 'title' => 'LiqPay', 'publish' => true]);
    $order = Order::create([
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'total_price' => 300, 'payment_method' => 'liqpay', 'payment_status' => PaymentStatus::PENDING,
    ]);

    $response = $this->get("/payment/{$order->id}");

    $response->assertDontSee(route('shop.checkout.thankyou', ['order' => $order->id]));
});
