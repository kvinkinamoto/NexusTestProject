<?php

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Events\OrderPaid;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers\LiqpayDriver;
use Illuminate\Support\Facades\Event;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'PaymentMethod'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    PaymentMethod::create(['code' => 'liqpay', 'provider' => 'liqpay', 'type' => 'gateway', 'title' => 'Online', 'publish' => true]);
});

function liqpaySuccessSignature(int $orderId): array
{
    $data = base64_encode(json_encode(['order_id' => $orderId, 'status' => 'success']));
    $signature = base64_encode(sha1(config('payment.liqpay.private_key').$data.config('payment.liqpay.private_key'), true));

    return ['data' => $data, 'signature' => $signature];
}

it('dispatches OrderPaid the moment a LiqPay callback marks the order paid', function () {
    Event::fake([OrderPaid::class]);

    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 300]);

    app(LiqpayDriver::class)->handleCallback(liqpaySuccessSignature($order->id));

    Event::assertDispatched(OrderPaid::class, fn (OrderPaid $event) => $event->order->id === $order->id);
});

it('does not dispatch OrderPaid when a callback fails signature verification', function () {
    Event::fake([OrderPaid::class]);

    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 300]);

    app(LiqpayDriver::class)->handleCallback(['data' => 'x', 'signature' => 'invalid']);

    Event::assertNotDispatched(OrderPaid::class);
    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PENDING);
});

it('does not dispatch OrderPaid for an unrelated order update', function () {
    Event::fake([OrderPaid::class]);

    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 300]);
    $order->update(['user_comment' => 'Please call after 6pm']);

    Event::assertNotDispatched(OrderPaid::class);
});

it('does not re-dispatch OrderPaid when an already-paid order is saved again unchanged', function () {
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 300]);
    app(LiqpayDriver::class)->handleCallback(liqpaySuccessSignature($order->id));

    Event::fake([OrderPaid::class]);
    $order->fresh()->update(['payment_status' => PaymentStatus::PAID]);

    Event::assertNotDispatched(OrderPaid::class);
});
