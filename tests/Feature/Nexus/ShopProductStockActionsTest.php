<?php

use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\ShopProduct\Services\Actions\EnsureProductStockAction;
use App\Nexus\Modules\ShopProduct\Services\Actions\ReduceProductStockAction;
use App\Nexus\Modules\ShopProduct\Services\Actions\ResolveProductStockAction;
use App\Nexus\Modules\ShopProduct\Services\Actions\RestoreProductStockAction;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
});

it('resolves the raw stock value', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 5]);

    expect(ResolveProductStockAction::handle($product))->toBe(5);
});

it('reduces stock but never below zero', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 3]);

    ReduceProductStockAction::handle($product, 2);
    expect($product->refresh()->stock)->toBe(1);

    ReduceProductStockAction::handle($product, 10);
    expect($product->refresh()->stock)->toBe(0);
});

it('restores stock', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 3]);

    RestoreProductStockAction::handle($product, 2);
    expect($product->refresh()->stock)->toBe(5);
});

it('leaves untracked (null) stock untouched by reduce/restore', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => null]);

    ReduceProductStockAction::handle($product, 2);
    RestoreProductStockAction::handle($product, 2);

    expect($product->refresh()->stock)->toBeNull();
});

it('throws when the requested quantity exceeds stock on hand', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => 1]);

    EnsureProductStockAction::handle($product, 5);
})->throws(DomainException::class);

it('does not throw for untracked (null) stock regardless of quantity', function () {
    $product = ShopProduct::create(['name' => 'iPhone', 'slug' => 'iphone', 'price' => 999, 'stock' => null]);

    EnsureProductStockAction::handle($product, 1000);
})->throwsNoExceptions();
