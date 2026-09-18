<?php

use App\Models\User;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    OrderStatus::create(['name' => 'New', 'slug' => 'new', 'is_default' => true, 'publish' => true]);
});

it('creates a gift order with a redeemable code even when the recipient has no account yet', function () {
    $buyer = User::factory()->create();
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5, 'publish' => true]);

    $response = $this->actingAs($buyer)->postJson('/api/orders', [
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'is_gift' => true, 'gift_recipient_email' => 'nobody@example.com',
        'products' => [['id' => $product->id, 'quantity' => 1]],
    ]);

    $response->assertOk();
    $order = Order::findOrFail($response->json('order.id'));
    expect($order->is_gift)->toBeTrue();
    expect($order->gift_code)->not->toBeNull();
    expect($order->recipient_user_id)->toBeNull();
});

it('does not require a gift_recipient_email at all', function () {
    $buyer = User::factory()->create();
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5, 'publish' => true]);

    $response = $this->actingAs($buyer)->postJson('/api/orders', [
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'is_gift' => true,
        'products' => [['id' => $product->id, 'quantity' => 1]],
    ]);

    $response->assertOk();
});

it('rejects gifting a product to yourself', function () {
    $buyer = User::factory()->create(['email' => 'me@example.com']);
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5, 'publish' => true]);

    $response = $this->actingAs($buyer)->postJson('/api/orders', [
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'is_gift' => true, 'gift_recipient_email' => 'me@example.com',
        'products' => [['id' => $product->id, 'quantity' => 1]],
    ]);

    $response->assertStatus(422);
    $response->assertJsonValidationErrors('gift_recipient_email');
});

it('still creates a non-gift order the same as before, with no gift code', function () {
    $buyer = User::factory()->create();
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5, 'publish' => true]);

    $response = $this->actingAs($buyer)->postJson('/api/orders', [
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'products' => [['id' => $product->id, 'quantity' => 1]],
    ]);

    $response->assertOk();
    $order = Order::findOrFail($response->json('order.id'));
    expect($order->is_gift)->toBeFalse();
    expect($order->gift_code)->toBeNull();
});
