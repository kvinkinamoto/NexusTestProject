<?php

use App\Models\User;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    // Isolate this test's throttle bucket from anything a previous test in
    // the same run may have hit — Laravel's default throttle key is
    // sha1(route domain + IP), not scoped to this route alone.
    Cache::flush();

    OrderStatus::create(['name' => 'New', 'slug' => 'new', 'is_default' => true, 'publish' => true]);
    $this->product = ShopProduct::create(['name' => 'Module', 'slug' => 'module', 'price' => 100, 'publish' => true]);
    // /api/orders now requires auth (checkout is gated so a License always
    // has a user_id to be issued to) — throttling is exercised as a
    // logged-in buyer, same as real traffic to this route.
    $this->buyer = User::factory()->create();
});

it('throttles order submissions past 10 per minute', function () {
    for ($i = 0; $i < 10; $i++) {
        $this->actingAs($this->buyer)->postJson('/api/orders', [
            'user_name' => 'Bob', 'user_phone' => '+380000000',
            'products' => [['id' => $this->product->id, 'quantity' => 1]],
        ])->assertOk();
    }

    $this->actingAs($this->buyer)->postJson('/api/orders', [
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'products' => [['id' => $this->product->id, 'quantity' => 1]],
    ])->assertStatus(429);
});
