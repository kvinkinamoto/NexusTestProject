<?php

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use App\Nexus\Modules\PaymentMethod\Services\Payments\Drivers\MonobankInstallmentDriver;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'PaymentMethod'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    $this->paymentMethod = PaymentMethod::create([
        'code' => 'mono_installment', 'provider' => 'monobank', 'type' => 'installment',
        'title' => 'Parts', 'publish' => true, 'count_installment' => 3,
    ]);

    $this->driver = app(MonobankInstallmentDriver::class);
});

function makeMonoOrder(): Order
{
    $order = Order::create([
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'payment_method' => 'mono_installment', 'total_price' => 300,
    ]);
    $order->products()->create(['product_id' => null, 'quantity' => 1, 'price' => 300, 'total_price' => 300, 'product_data' => ['name' => 'Shirt']]);

    return $order;
}

it('creates a payment and stores the gateway order id', function () {
    Http::fake([
        '*/api/order/create' => Http::response(['order_id' => 'mono-abc-123'], 200),
    ]);

    $order = makeMonoOrder();

    $response = $this->driver->createPayment($order);

    expect($response->getTargetUrl())->toContain((string) $order->id);
    expect($order->fresh()->bank_order_id)->toBe('mono-abc-123');
});

it('redirects with an error when the gateway call fails', function () {
    Http::fake([
        '*/api/order/create' => Http::response([], 400),
    ]);

    $order = makeMonoOrder();

    $response = $this->driver->createPayment($order);

    expect($response->getSession()->get('payment_error'))->not->toBeNull();
    expect($order->fresh()->bank_order_id)->toBeNull();
});

it('marks the order as paid on a valid SUCCESS callback', function () {
    $order = makeMonoOrder();
    $order->update(['bank_order_id' => 'mono-abc-123']);

    $payload = ['store_order_id' => (string) $order->id, 'state' => 'SUCCESS', 'order_sub_state' => 'ACTIVE'];
    $payload['signature'] = base64_encode(hash_hmac('sha256', json_encode($payload, JSON_UNESCAPED_UNICODE), config('payment.monobank.secret'), true));

    $this->driver->handleCallback($payload);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PAID);
});

it('ignores a callback with an invalid signature', function () {
    $order = makeMonoOrder();

    $this->driver->handleCallback([
        'store_order_id' => (string) $order->id,
        'state' => 'SUCCESS',
        'order_sub_state' => 'ACTIVE',
        'signature' => 'not-a-real-signature',
    ]);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PENDING);
});

it('marks the order cancelled when the gateway reports FAIL', function () {
    $order = makeMonoOrder();
    $order->update(['bank_order_id' => 'mono-abc-123']);

    Http::fake([
        '*/api/order/state' => Http::response(['state' => 'FAIL'], 200),
    ]);

    $this->driver->checkPayment($order);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::CANCELLED);
});

it('confirms a store-confirm-pending order and applies the resulting status', function () {
    $order = makeMonoOrder();
    $order->update(['bank_order_id' => 'mono-abc-123']);

    Http::fake([
        '*/api/order/state' => Http::response(['state' => 'IN_PROCESS', 'order_sub_state' => 'WAITING_FOR_STORE_CONFIRM', 'order_id' => 'mono-abc-123'], 200),
        '*/api/order/confirm' => Http::response(['state' => 'SUCCESS', 'order_sub_state' => 'DONE'], 200),
    ]);

    $this->driver->checkPayment($order);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PAID);
});
