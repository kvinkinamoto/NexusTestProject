<?php

use App\Models\User;
use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use App\Nexus\Modules\Bonus\Models\BonusRule;
use App\Nexus\Modules\BonusTransaction\Models\BonusTransaction;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\ProductReview\Models\Review;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Bonus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();
    $this->artisan('nexus:module:install', ['name' => 'ProductReview'])->run();

    BonusRule::create(['type' => BonusTypeEnum::REVIEW, 'amount' => 50, 'delay_days' => 0, 'is_active' => true]);
});

function completedOrderFor(User $user, ShopProduct $product, int $quantity = 1): Order
{
    $order = Order::create(['user_id' => $user->id, 'user_name' => $user->name, 'user_phone' => '1', 'completed_at' => now()]);
    $order->products()->create(['product_id' => $product->id, 'quantity' => $quantity, 'price' => 100, 'total_price' => 100 * $quantity]);

    return $order;
}

it('awards a bonus for an approved review from a verified purchaser', function () {
    $user = User::factory()->create();
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
    completedOrderFor($user, $product);

    $review = Review::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'rating' => 5,
        'publish' => true,
    ]);

    $tx = BonusTransaction::query()->where('user_id', $user->id)->where('type', BonusTypeEnum::REVIEW)->first();
    expect($tx)->not->toBeNull();
    expect((float) $tx->amount)->toBe(50.0);
    expect($tx->source_id)->toBe($review->id);
});

it('awards nothing for an unpublished review', function () {
    $user = User::factory()->create();
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
    completedOrderFor($user, $product);

    Review::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'rating' => 5,
        'publish' => false,
    ]);

    expect(BonusTransaction::query()->where('type', BonusTypeEnum::REVIEW)->exists())->toBeFalse();
});

it('awards nothing when the reviewer never completed a purchase of that product', function () {
    $user = User::factory()->create();
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);

    Review::create([
        'product_id' => $product->id,
        'user_id' => $user->id,
        'user_name' => $user->name,
        'rating' => 5,
        'publish' => true,
    ]);

    expect(BonusTransaction::query()->where('type', BonusTypeEnum::REVIEW)->exists())->toBeFalse();
});

it('caps review bonuses at the number of distinct completed purchases of that product', function () {
    $user = User::factory()->create();
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
    completedOrderFor($user, $product); // exactly one completed purchase

    $review1 = Review::create(['product_id' => $product->id, 'user_id' => $user->id, 'user_name' => $user->name, 'rating' => 5, 'publish' => true]);
    $review2 = Review::create(['product_id' => $product->id, 'user_id' => $user->id, 'user_name' => $user->name, 'rating' => 4, 'publish' => true]);

    expect(BonusTransaction::query()->where('type', BonusTypeEnum::REVIEW)->where('user_id', $user->id)->count())->toBe(1);
    expect(BonusTransaction::query()->where('source_id', $review1->id)->exists())->toBeTrue();
    expect(BonusTransaction::query()->where('source_id', $review2->id)->exists())->toBeFalse();
});

it('does not double-award when the same review row is saved again', function () {
    $user = User::factory()->create();
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
    completedOrderFor($user, $product);

    $review = Review::create(['product_id' => $product->id, 'user_id' => $user->id, 'user_name' => $user->name, 'rating' => 5, 'publish' => true]);
    $review->update(['comment' => 'edited']);

    expect(BonusTransaction::query()->where('source_id', $review->id)->where('type', BonusTypeEnum::REVIEW)->count())->toBe(1);
});
