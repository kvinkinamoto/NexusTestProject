<?php

use App\Models\User;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'PaymentMethod'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    PaymentMethod::create(['code' => 'liqpay', 'provider' => 'liqpay', 'type' => 'gateway', 'title' => 'Online', 'publish' => true]);
});

it('renders the payment form for a guest order', function () {
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 300]);

    $this->get("/payment/{$order->id}")->assertOk();
});

it('renders the payment form for the owning user\'s order', function () {
    $user = User::factory()->create();
    $order = Order::create(['user_id' => $user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 300]);

    $this->actingAs($user)->get("/payment/{$order->id}")->assertOk();
});

it('404s for another user\'s order', function () {
    $owner = User::factory()->create();
    $intruder = User::factory()->create();
    $order = Order::create(['user_id' => $owner->id, 'user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 300]);

    $this->actingAs($intruder)->get("/payment/{$order->id}")->assertNotFound();
});
