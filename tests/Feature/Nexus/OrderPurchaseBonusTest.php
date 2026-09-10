<?php

use App\Models\User;
use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use App\Nexus\Modules\Bonus\Models\BonusRule;
use App\Nexus\Modules\BonusTransaction\Enums\BonusTransactionStatusEnum;
use App\Nexus\Modules\BonusTransaction\Models\BonusTransaction;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\Order\Services\OrderService;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Bonus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    $this->service = app(OrderService::class);
    $this->user = User::factory()->create();

    OrderStatus::create(['name' => 'New', 'slug' => 'new', 'is_default' => true, 'publish' => true]);
    OrderStatus::create(['name' => 'Delivered', 'slug' => 'delivered', 'publish' => true]);

    BonusRule::create(['type' => BonusTypeEnum::PURCHASE, 'amount' => 0, 'delay_days' => 0, 'validity_days' => 180, 'is_active' => true]);
});

it('awards a purchase bonus once the order is marked delivered', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'bonuses' => 5, 'stock' => 5]);
    $order = Order::create(['user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '1']);
    $order->products()->create(['product_id' => $product->id, 'quantity' => 2, 'price' => 100, 'total_price' => 200]);

    $delivered = OrderStatus::where('slug', 'delivered')->firstOrFail();
    $order->update(['status_id' => $delivered->id]);

    $bonus = BonusTransaction::where('source_type', $order->getMorphClass())->where('source_id', $order->id)->first();

    expect($bonus)->not->toBeNull();
    expect((float) $bonus->amount)->toBe(10.0);
    expect($bonus->status)->toBe(BonusTransactionStatusEnum::AVAILABLE);
});

it('does not award a bonus for a guest order', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'bonuses' => 5, 'stock' => 5]);
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '1']);
    $order->products()->create(['product_id' => $product->id, 'quantity' => 1, 'price' => 100, 'total_price' => 100]);

    $delivered = OrderStatus::where('slug', 'delivered')->firstOrFail();
    $order->update(['status_id' => $delivered->id]);

    expect(BonusTransaction::count())->toBe(0);
});

it('does not award a bonus twice for the same order', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'bonuses' => 5, 'stock' => 5]);
    $order = Order::create(['user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '1']);
    $order->products()->create(['product_id' => $product->id, 'quantity' => 1, 'price' => 100, 'total_price' => 100]);

    $delivered = OrderStatus::where('slug', 'delivered')->firstOrFail();
    $order->update(['status_id' => $delivered->id]);
    $order->update(['admin_comment' => 'touching the row again']);

    expect(BonusTransaction::where('source_id', $order->id)->count())->toBe(1);
});

it('does not award a bonus for a product with no bonuses configured', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5]);
    $order = Order::create(['user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '1']);
    $order->products()->create(['product_id' => $product->id, 'quantity' => 1, 'price' => 100, 'total_price' => 100]);

    $delivered = OrderStatus::where('slug', 'delivered')->firstOrFail();
    $order->update(['status_id' => $delivered->id]);

    expect(BonusTransaction::count())->toBe(0);
});
