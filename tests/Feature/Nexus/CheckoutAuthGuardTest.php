<?php

use App\Models\User;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

/**
 * Checkout requires an account — a License row needs a user_id to be
 * issued to (IssueLicensesOnOrderPaid skips guest orders), so a guest who
 * paid without logging in could never receive or download their module.
 * Guarding /checkout and POST /api/orders behind auth closes that gap at
 * the source instead of relying on a later claim/link step.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    OrderStatus::create(['name' => 'New', 'slug' => 'new', 'is_default' => true, 'publish' => true]);
    $this->product = ShopProduct::create(['name' => 'Module', 'slug' => 'module', 'price' => 100, 'publish' => true]);
});

it('redirects a guest away from /checkout to login', function () {
    $this->get('/checkout')->assertRedirect(route('login'));
});

it('lets a logged-in user reach /checkout', function () {
    $this->actingAs(User::factory()->create())->get('/checkout')->assertOk();
});

it('rejects an order submission from a guest', function () {
    $response = $this->postJson('/api/orders', [
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'products' => [['id' => $this->product->id, 'quantity' => 1]],
    ]);

    $response->assertStatus(401);
});

it('accepts an order submission from a logged-in user', function () {
    $response = $this->actingAs(User::factory()->create())->postJson('/api/orders', [
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'products' => [['id' => $this->product->id, 'quantity' => 1]],
    ]);

    $response->assertOk();
});
