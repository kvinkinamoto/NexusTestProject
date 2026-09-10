<?php

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers\LiqpayDriver;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'PaymentMethod'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    PaymentMethod::create(['code' => 'liqpay', 'provider' => 'liqpay', 'type' => 'gateway', 'title' => 'Online', 'publish' => true]);

    $this->driver = app(LiqpayDriver::class);
});

function makeLiqpayOrder(): Order
{
    return Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 300]);
}

it('renders the LiqPay checkout form and stores the bank order id', function () {
    $order = makeLiqpayOrder();

    $response = $this->driver->createPayment($order);
    $html = $response->getContent();

    expect($html)->toContain('name="data"');
    expect($html)->toContain('name="signature"');
    expect($order->fresh()->bank_order_id)->toBe((string) $order->id);
});

it('marks the order as paid on a valid signed success callback', function () {
    $order = makeLiqpayOrder();

    $data = base64_encode(json_encode(['order_id' => $order->id, 'status' => 'success']));
    $signature = base64_encode(sha1(config('payment.liqpay.private_key').$data.config('payment.liqpay.private_key'), true));

    $this->driver->handleCallback(['data' => $data, 'signature' => $signature]);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PAID);
});

it('marks the order cancelled on a valid signed failure callback', function () {
    $order = makeLiqpayOrder();

    $data = base64_encode(json_encode(['order_id' => $order->id, 'status' => 'failure']));
    $signature = base64_encode(sha1(config('payment.liqpay.private_key').$data.config('payment.liqpay.private_key'), true));

    $this->driver->handleCallback(['data' => $data, 'signature' => $signature]);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::CANCELLED);
});

it('ignores a callback with an invalid signature', function () {
    $order = makeLiqpayOrder();

    $data = base64_encode(json_encode(['order_id' => $order->id, 'status' => 'success']));

    $this->driver->handleCallback(['data' => $data, 'signature' => 'not-valid']);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PENDING);
});

it('ignores a callback missing data or signature', function () {
    $order = makeLiqpayOrder();

    $this->driver->handleCallback([]);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PENDING);
});
