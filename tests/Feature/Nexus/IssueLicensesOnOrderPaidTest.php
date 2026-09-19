<?php

use App\Models\User;
use App\Nexus\Modules\License\Listeners\IssueLicensesOnOrderPaid;
use App\Nexus\Modules\License\Models\License;
use App\Nexus\Modules\License\Notifications\LicensesIssuedNotification;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Events\OrderPaid;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;

/**
 * License's Listeners/ folder is only auto-wired from modules the DB marks
 * enabled at the moment NexusServiceProvider::boot() runs — which happens
 * before this file's beforeEach() (and any nexus:module:install call in it)
 * ever executes, a structural timing gap this suite already documents for
 * other events (see PluginEventCoverageTest.php's docblock). Every test
 * there works around it the same way: register the real listener by hand.
 * Verified separately via `php artisan tinker` that production boot (where
 * License really is installed ahead of time) wires it automatically.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'PaymentMethod'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();
    $this->artisan('nexus:module:install', ['name' => 'License'])->run();

    Event::listen(OrderPaid::class, IssueLicensesOnOrderPaid::class);

    PaymentMethod::create(['code' => 'liqpay', 'provider' => 'liqpay', 'type' => 'gateway', 'title' => 'Online', 'publish' => true]);

    $this->user = User::factory()->create();
    $this->productA = ShopProduct::create(['name' => 'Module A', 'slug' => 'module-a', 'price' => 500, 'publish' => true]);
    $this->productB = ShopProduct::create(['name' => 'Module B', 'slug' => 'module-b', 'price' => 300, 'publish' => true]);
});

function payOrder(Order $order): void
{
    $order->update(['payment_status' => PaymentStatus::PAID]);
}

it('issues one license per unit of quantity on each order line once the order is paid', function () {
    $order = Order::create(['user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 1100]);
    $order->products()->create(['product_id' => $this->productA->id, 'quantity' => 1, 'price' => 500, 'total_price' => 500]);
    $order->products()->create(['product_id' => $this->productB->id, 'quantity' => 2, 'price' => 300, 'total_price' => 600]);

    payOrder($order);

    $licenses = License::query()->where('order_id', $order->id)->get();

    expect($licenses)->toHaveCount(3);
    expect($licenses->where('product_id', $this->productA->id))->toHaveCount(1);
    expect($licenses->where('product_id', $this->productB->id))->toHaveCount(2);
    expect($licenses->pluck('user_id')->unique()->all())->toBe([$this->user->id]);
    expect($licenses->pluck('license_key')->unique())->toHaveCount(3);
    expect($licenses->every(fn (License $l) => $l->isActive() && $l->max_activations === 1))->toBeTrue();
});

it('does not issue licenses for a guest order with no user account', function () {
    $order = Order::create(['user_name' => 'Guest', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 500]);
    $order->products()->create(['product_id' => $this->productA->id, 'quantity' => 1, 'price' => 500, 'total_price' => 500]);

    payOrder($order);

    expect(License::query()->where('order_id', $order->id)->exists())->toBeFalse();
});

it('does not double-issue licenses if OrderPaid ever fires twice for the same order', function () {
    $order = Order::create(['user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 500]);
    $order->products()->create(['product_id' => $this->productA->id, 'quantity' => 1, 'price' => 500, 'total_price' => 500]);

    payOrder($order);
    expect(License::query()->where('order_id', $order->id)->count())->toBe(1);

    event(new OrderPaid($order->fresh()));

    expect(License::query()->where('order_id', $order->id)->count())->toBe(1);
});

it('emails the buyer once with their issued licenses', function () {
    Notification::fake();

    $order = Order::create(['user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 1100]);
    $order->products()->create(['product_id' => $this->productA->id, 'quantity' => 1, 'price' => 500, 'total_price' => 500]);
    $order->products()->create(['product_id' => $this->productB->id, 'quantity' => 2, 'price' => 300, 'total_price' => 600]);

    payOrder($order);

    Notification::assertSentTo(
        $this->user,
        LicensesIssuedNotification::class,
        fn (LicensesIssuedNotification $notification) => $notification->toMail($this->user)->subject === "Замовлення #{$order->id} оплачено — ваші ліцензії готові",
    );
    Notification::assertSentTimes(LicensesIssuedNotification::class, 1);
});

it('does not email anyone for a guest order', function () {
    Notification::fake();

    $order = Order::create(['user_name' => 'Guest', 'user_phone' => '+380000000', 'payment_method' => 'liqpay', 'total_price' => 500]);
    $order->products()->create(['product_id' => $this->productA->id, 'quantity' => 1, 'price' => 500, 'total_price' => 500]);

    payOrder($order);

    Notification::assertNothingSent();
});
