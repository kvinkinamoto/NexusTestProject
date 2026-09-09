<?php

use App\Models\User;
use App\Nexus\Modules\Cart\Services\CartService;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Cart'])->run();

    $this->service = app(CartService::class);
    $this->user = User::factory()->create();
});

it('adds a product to a new cart', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 10]);

    $this->service->add($this->user, $product->id, 2);

    $cart = $this->service->getCart($this->user);
    expect($cart->products()->where('product_id', $product->id)->first()->quantity)->toBe(2);
});

it('accumulates quantity when adding the same product again', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 10]);

    $this->service->add($this->user, $product->id, 2);
    $this->service->add($this->user, $product->id, 3);

    $cart = $this->service->getCart($this->user);
    expect($cart->products()->where('product_id', $product->id)->first()->quantity)->toBe(5);
});

it('refuses to add more than is in stock', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 1]);

    $this->service->add($this->user, $product->id, 5);
})->throws(DomainException::class);

it('updates the quantity of an existing item', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 10]);
    $this->service->add($this->user, $product->id, 1);

    $this->service->updateQuantity($this->user, $product->id, 5);

    $cart = $this->service->getCart($this->user);
    expect($cart->products()->where('product_id', $product->id)->first()->quantity)->toBe(5);
});

it('removes the item when quantity is updated to zero', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 10]);
    $this->service->add($this->user, $product->id, 1);

    $this->service->updateQuantity($this->user, $product->id, 0);

    $cart = $this->service->getCart($this->user);
    expect($cart->products()->count())->toBe(0);
});

it('removes an item from the cart', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 10]);
    $this->service->add($this->user, $product->id, 1);

    $this->service->remove($this->user, $product->id);

    $cart = $this->service->getCart($this->user);
    expect($cart->products()->count())->toBe(0);
});

it('syncs a batch of products, capping quantity at available stock', function () {
    $a = ShopProduct::create(['name' => 'A', 'slug' => 'a', 'price' => 10, 'stock' => 3]);
    $b = ShopProduct::create(['name' => 'B', 'slug' => 'b', 'price' => 20, 'stock' => null]);

    $this->service->sync($this->user, [
        ['id' => $a->id, 'quantity' => 10],
        ['id' => $b->id, 'quantity' => 4],
    ]);

    $cart = $this->service->getCart($this->user);
    expect($cart->products()->where('product_id', $a->id)->first()->quantity)->toBe(3);
    expect($cart->products()->where('product_id', $b->id)->first()->quantity)->toBe(4);
});

it('calculates the cart total from each line\'s effective price', function () {
    $a = ShopProduct::create(['name' => 'A', 'slug' => 'a', 'price' => 100, 'price_discount' => 80]);
    $b = ShopProduct::create(['name' => 'B', 'slug' => 'b', 'price' => 50]);

    $this->service->add($this->user, $a->id, 2);
    $this->service->add($this->user, $b->id, 1);

    $cart = $this->service->getCart($this->user);
    $total = $this->service->calculateTotal($cart->products()->with('product')->get());

    expect($total)->toBe(210.0);
});

it('always returns the same cart for the same user', function () {
    $first = $this->service->getCart($this->user);
    $second = $this->service->getCart($this->user);

    expect($first->id)->toBe($second->id);
});
