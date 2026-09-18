<?php

use App\Models\User;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Cart'])->run();

    $this->user = User::factory()->create();
});

it('returns an empty session-scoped cart for a guest instead of rejecting the request', function () {
    $this->getJson('/api/cart')
        ->assertOk()
        ->assertJsonCount(0, 'products');
});

it('lets a guest add a product to their session cart without authenticating', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 10]);

    $this->postJson('/api/cart/add', ['id' => $product->id, 'quantity' => 1])
        ->assertOk()
        ->assertJson(['success' => true]);
});

it('adds a product via the API and returns it in the cart', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 10]);

    $this->actingAs($this->user)
        ->postJson('/api/cart/add', ['id' => $product->id, 'quantity' => 2])
        ->assertOk()
        ->assertJson(['success' => true]);

    $response = $this->actingAs($this->user)->getJson('/api/cart');

    $response->assertOk()
        ->assertJsonPath('total_price', 1998)
        ->assertJsonCount(1, 'products')
        ->assertJsonPath('products.0.quantity', 2);
});

it('returns a 400 with a message when adding more than available stock', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 1]);

    $this->actingAs($this->user)
        ->postJson('/api/cart/add', ['id' => $product->id, 'quantity' => 5])
        ->assertStatus(400)
        ->assertJson(['success' => false]);
});

it('removes a product via the API', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 10]);
    $this->actingAs($this->user)->postJson('/api/cart/add', ['id' => $product->id, 'quantity' => 1]);

    $this->actingAs($this->user)
        ->postJson('/api/cart/remove', ['id' => $product->id])
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->actingAs($this->user)->getJson('/api/cart')->assertJsonCount(0, 'products');
});
