<?php

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;

/**
 * LiqPay's result_url (the browser return after paying) used to always
 * redirect to '/' with a flash message the storefront layout never even
 * renders — the buyer saw no confirmation either way, regardless of
 * whether payment actually succeeded. It now redirects to the real
 * thank-you page, which reads the order's true payment_status.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();
});

it('redirects to the thank-you page for the given order', function () {
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'total_price' => 300]);

    $this->get("/payment/result/{$order->id}")->assertRedirect(route('shop.checkout.thankyou', ['order' => $order->id]));
});

it('shows the paid confirmation once payment_status is PAID', function () {
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'total_price' => 300, 'payment_status' => PaymentStatus::PAID]);

    $this->get(route('shop.checkout.thankyou', ['order' => $order->id]))
        ->assertOk()
        ->assertSee('Оплачено')
        ->assertSee('Оплату отримано');
});

it('shows a pending message while payment_status is still PENDING', function () {
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'total_price' => 300]);

    $this->get(route('shop.checkout.thankyou', ['order' => $order->id]))
        ->assertOk()
        ->assertSee('Очікує оплати')
        ->assertDontSee('Оплату отримано');
});

it('shows a cancelled message when payment_status is CANCELLED', function () {
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'total_price' => 300, 'payment_status' => PaymentStatus::CANCELLED]);

    $this->get(route('shop.checkout.thankyou', ['order' => $order->id]))
        ->assertOk()
        ->assertSee('Скасовано')
        ->assertSee('Оплату не підтверджено');
});

it('404s for a non-existent order id', function () {
    $this->get('/payment/result/999999')->assertNotFound();
});
