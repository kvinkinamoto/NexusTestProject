<?php

use App\Models\User;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\Wishlist\Models\WishlistItem;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Wishlist'])->run();

    $this->user = User::factory()->create();
    $this->product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'publish' => true]);
});

it('returns an empty session-scoped wishlist for a guest instead of rejecting the request', function () {
    $this->getJson('/api/wishlist')
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('lets a guest add a product to their session wishlist without authenticating', function () {
    $this->postJson('/api/wishlist/add', ['product_id' => $this->product->id])
        ->assertOk()
        ->assertJson(['success' => true]);
});

it('rejects an unauthenticated request for the auth-only details endpoint', function () {
    $this->getJson('/api/wishlist/details')->assertUnauthorized();
});

it('adds and lists a product for the authenticated user', function () {
    $this->actingAs($this->user)
        ->postJson('/api/wishlist/add', ['product_id' => $this->product->id])
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->actingAs($this->user)
        ->getJson('/api/wishlist')
        ->assertOk()
        ->assertJsonFragment(['id' => $this->product->id]);
});

it('removes a product for the authenticated user', function () {
    WishlistItem::create(['user_id' => $this->user->id, 'product_id' => $this->product->id]);

    $this->actingAs($this->user)
        ->postJson('/api/wishlist/remove', ['product_id' => $this->product->id])
        ->assertOk();

    expect(WishlistItem::query()->where('user_id', $this->user->id)->exists())->toBeFalse();
});

it('resolves product ids to details without authentication', function () {
    $this->postJson('/api/wishlist/resolve', ['items' => [$this->product->id]])
        ->assertOk()
        ->assertJsonFragment(['title' => 'Shirt']);
});
