<?php

use App\Models\User;
use App\Nexus\Modules\ActivityLog\Models\ActivityLog;
use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\Promotion\Models\Promotion;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'DiscountRule'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Promotion'])->run();
});

it('logs a created event with dirty attributes for a plain (non-translated) attribute', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);

    $activity = ActivityLog::query()->forSubject($product)->latest('id')->first();

    expect($activity)->not->toBeNull();
    expect($activity->event)->toBe('created');
    expect($activity->attribute_changes['attributes']['slug'])->toBe('shirt');
});

it('logs an updated event only for dirty attributes and skips a no-op save', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
    $countAfterCreate = ActivityLog::query()->forSubject($product)->count();

    $product->update(['price' => 150]);

    $updated = ActivityLog::query()->forSubject($product)->where('event', 'updated')->latest('id')->first();
    expect($updated)->not->toBeNull();
    expect($updated->attribute_changes['attributes']['price'])->toBe('150.00');
    expect($updated->attribute_changes['attributes'])->not->toHaveKey('name');

    // Saving with nothing actually changed must not add a new log row.
    $product->save();
    expect(ActivityLog::query()->forSubject($product)->count())->toBe($countAfterCreate + 1);
});

it('decodes a translated attribute back into an array instead of a raw JSON string', function () {
    $rule = DiscountRule::create(['name' => 'Sale', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value]);

    $promotion = Promotion::create([
        'name' => ['en' => 'Summer Sale', 'uk' => 'Літній розпродаж'],
        'discount_rule_id' => $rule->id,
    ]);

    $activity = ActivityLog::query()->forSubject($promotion)->where('event', 'created')->latest('id')->first();

    expect($activity->attribute_changes['attributes']['name'])->toBe([
        'en' => 'Summer Sale',
        'uk' => 'Літній розпродаж',
    ]);
});

it('records the acting user as the causer', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);

    $activity = ActivityLog::query()->forSubject($product)->latest('id')->first();

    expect($activity->causer_id)->toBe($user->id);
    expect($activity->causer_type)->toBe($user->getMorphClass());
});

it('excludes password and remember_token from the User model\'s own activity log', function () {
    $user = User::factory()->create(['name' => 'Bob']);

    $user->update(['password' => 'a-new-plaintext-value-before-hashing', 'name' => 'Bobby']);

    $activity = ActivityLog::query()->forSubject($user)->where('event', 'updated')->latest('id')->first();

    expect($activity->attribute_changes['attributes'])->toHaveKey('name');
    expect($activity->attribute_changes['attributes'])->not->toHaveKey('password');
    expect($activity->attribute_changes['attributes'])->not->toHaveKey('remember_token');
});
