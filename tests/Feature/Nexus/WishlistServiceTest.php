<?php

use App\Models\User;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\Wishlist\Models\WishlistItem;
use App\Nexus\Modules\Wishlist\Repositories\WishlistRepository;
use App\Nexus\Modules\Wishlist\Services\WishlistService;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Wishlist'])->run();

    $this->service = app(WishlistService::class);
    $this->repository = app(WishlistRepository::class);
    $this->user = User::factory()->create();
    $this->product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'publish' => true]);
});

it('adds a product to the wishlist idempotently', function () {
    $this->service->add($this->user, $this->product->id);
    $this->service->add($this->user, $this->product->id);

    expect(WishlistItem::query()->where('user_id', $this->user->id)->count())->toBe(1);
});

it('removes a product from the wishlist', function () {
    $this->service->add($this->user, $this->product->id);

    $this->service->remove($this->user, $this->product->id);

    expect(WishlistItem::query()->where('user_id', $this->user->id)->exists())->toBeFalse();
});

it('syncs a batch of product ids, ignoring duplicates', function () {
    $second = ShopProduct::create(['name' => 'Hat', 'slug' => 'hat', 'price' => 50, 'publish' => true]);
    $this->service->add($this->user, $this->product->id);

    $this->service->sync($this->user, [$this->product->id, $second->id]);

    expect(WishlistItem::query()->where('user_id', $this->user->id)->count())->toBe(2);
});

it('lists only published products in the detail view', function () {
    $unpublished = ShopProduct::create(['name' => 'Hidden', 'slug' => 'hidden', 'price' => 10, 'publish' => false]);
    $this->service->add($this->user, $this->product->id);
    $this->service->add($this->user, $unpublished->id);

    $details = $this->repository->getDetailsForUser($this->user);

    expect($details->pluck('id')->all())->toBe([$this->product->id]);
});
