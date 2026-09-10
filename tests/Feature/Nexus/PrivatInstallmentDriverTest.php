<?php

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers\PrivatInstallmentDriver;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'PaymentMethod'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    PaymentMethod::create([
        'code' => 'privat_installment', 'provider' => 'privatbank', 'type' => 'installment',
        'title' => 'Parts', 'publish' => true, 'count_installment' => 3,
    ]);

    $this->driver = app(PrivatInstallmentDriver::class);
});

function makePrivatOrder(): Order
{
    $order = Order::create([
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'payment_method' => 'privat_installment', 'total_price' => 300,
    ]);
    $order->products()->create(['product_id' => null, 'quantity' => 1, 'price' => 300, 'total_price' => 300, 'product_data' => ['name' => 'Shirt']]);

    return $order;
}

it('creates a payment and stores the gateway order id', function () {
    Http::fake([
        '*/payment/create' => Http::response(['state' => 'SUCCESS', 'orderId' => 'privat-abc', 'token' => 'tok-123'], 200),
    ]);

    $order = makePrivatOrder();

    $response = $this->driver->createPayment($order);

    expect($response->getTargetUrl())->toContain('tok-123');
    expect($order->fresh()->bank_order_id)->toBe('privat-abc');
});

it('redirects with an error when the gateway rejects the order', function () {
    Http::fake([
        '*/payment/create' => Http::response(['state' => 'FAILURE'], 200),
    ]);

    $order = makePrivatOrder();

    $response = $this->driver->createPayment($order);

    expect($response->getSession()->get('payment_error'))->not->toBeNull();
    expect($order->fresh()->bank_order_id)->toBeNull();
});

it('marks the order as paid on a valid SUCCESS callback', function () {
    $order = makePrivatOrder();

    $payload = [
        'storeId' => config('payment.privat.store_id'),
        'orderId' => (string) $order->id,
        'paymentState' => 'SUCCESS',
        'message' => 'ok',
    ];
    $password = config('payment.privat.password');
    $payload['signature'] = base64_encode(sha1($password.$payload['storeId'].$payload['orderId'].$payload['paymentState'].$payload['message'].$password, true));

    $this->driver->handleCallback($payload);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PAID);
});

it('ignores a callback with an invalid signature', function () {
    $order = makePrivatOrder();

    $this->driver->handleCallback([
        'storeId' => config('payment.privat.store_id'),
        'orderId' => (string) $order->id,
        'paymentState' => 'SUCCESS',
        'message' => 'ok',
        'signature' => 'bad-signature',
    ]);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PENDING);
});

it('marks the order cancelled when checkPayment reports FAIL', function () {
    $order = makePrivatOrder();
    $order->update(['bank_order_id' => 'privat-abc']);

    Http::fake([
        '*/payment/state' => Http::response(['paymentState' => 'FAIL'], 200),
    ]);

    $this->driver->checkPayment($order);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::CANCELLED);
});
