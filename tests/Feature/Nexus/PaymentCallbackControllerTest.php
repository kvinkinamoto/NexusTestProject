<?php

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'PaymentMethod'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    PaymentMethod::create(['code' => 'liqpay', 'provider' => 'liqpay', 'type' => 'gateway', 'title' => 'Online', 'publish' => true]);
});

it('processes a valid liqpay webhook and returns json for a non-html client', function () {
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 300]);

    $data = base64_encode(json_encode(['order_id' => $order->id, 'status' => 'success']));
    $signature = base64_encode(sha1(config('payment.liqpay.private_key').$data.config('payment.liqpay.private_key'), true));

    $this->postJson('/payment/callback/liqpay', ['data' => $data, 'signature' => $signature])
        ->assertOk()
        ->assertJson(['status' => 'ok']);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PAID);
});

it('returns a 500 json error for an unknown provider', function () {
    $this->postJson('/payment/callback/unknown_provider', [])
        ->assertStatus(500)
        ->assertJson(['status' => 'error']);
});
