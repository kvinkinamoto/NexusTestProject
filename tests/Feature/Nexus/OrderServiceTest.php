<?php

use App\Models\User;
use App\Nexus\Modules\Cart\Services\CartService;
use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Enums\ValueType;
use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Services\OrderService;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use App\Nexus\Modules\PromoCode\Models\PromoCode;
use App\Nexus\Modules\PromoCode\Models\PromoCodeUsage;
use App\Nexus\Modules\Promotion\Models\Promotion;
use App\Nexus\Modules\Promotion\Models\PromotionUsage;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Nodex\Nexus\Services\SettingsBuilder;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'DiscountRule'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Promotion'])->run();
    $this->artisan('nexus:module:install', ['name' => 'PromoCode'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Cart'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    $this->service = app(OrderService::class);
    $this->cartService = app(CartService::class);
    $this->user = User::factory()->create();

    OrderStatus::create(['name' => 'New', 'slug' => 'new', 'is_default' => true, 'publish' => true]);
    OrderStatus::create(['name' => 'Confirmed', 'slug' => 'confirmed', 'publish' => true]);
    OrderStatus::create(['name' => 'Cancelled', 'slug' => 'cancelled', 'publish' => true]);
    OrderStatus::create(['name' => 'Delivered', 'slug' => 'delivered', 'publish' => true]);
});

it('creates an order from an authed user\'s cart and clears it', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5]);
    $this->cartService->add($this->user, null, $product->id, 2);

    $order = $this->service->createOrder($this->user, ['user_name' => 'Bob', 'user_phone' => '+380000000']);

    expect($order->user_id)->toBe($this->user->id);
    expect((float) $order->products_price)->toBe(200.0);
    expect((float) $order->total_price)->toBe(200.0);
    expect($order->status?->slug)->toBe('new');
    expect($order->payment_status)->toBe(PaymentStatus::PENDING);
    expect($order->products()->count())->toBe(1);
    expect($order->products()->first()->quantity)->toBe(2);
    expect($order->history()->count())->toBe(1);

    $cart = $this->cartService->resolveCart($this->user, null);
    expect($cart->products()->count())->toBe(0);
});

it('throws when the cart is empty', function () {
    $this->service->createOrder($this->user, ['user_name' => 'Bob', 'user_phone' => '+380000000']);
})->throws(DomainException::class);

it('creates a guest order by resolving products directly from the request', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 50, 'stock' => 5]);

    $order = $this->service->createOrder(null, [
        'user_name' => 'Guest',
        'user_phone' => '+380000000',
        'products' => [['id' => $product->id, 'quantity' => 3]],
    ]);

    expect($order->user_id)->toBeNull();
    expect((float) $order->products_price)->toBe(150.0);
    expect($order->products()->first()->quantity)->toBe(3);
});

it('rejects a guest checkout when allow_guest_checkout is disabled', function () {
    SettingsBuilder::set('order', 'allow_guest_checkout', false);

    $this->service->createOrder(null, ['user_name' => 'Guest', 'user_phone' => '+380000000', 'products' => []]);
})->throws(DomainException::class);

it('throws when the requested quantity exceeds available stock', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 50, 'stock' => 1]);
    $this->cartService->add($this->user, null, $product->id, 1);
    $product->update(['stock' => 0]);

    $this->service->createOrder($this->user, ['user_name' => 'Bob', 'user_phone' => '+380000000']);
})->throws(DomainException::class);

it('applies a cart-level promo code and records its usage', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5]);
    $rule = DiscountRule::create(['name' => '10% off cart', 'type' => DiscountRuleType::CART_THRESHOLD->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 10]);
    $code = PromoCode::create(['code' => 'SAVE10', 'discount_rule_id' => $rule->id, 'publish' => true]);
    $this->cartService->add($this->user, null, $product->id, 1);

    $order = $this->service->createOrder($this->user, [
        'user_name' => 'Bob',
        'user_phone' => '+380000000',
        'promo_code' => 'save10',
    ]);

    expect($order->promo_code_id)->toBe($code->id);
    expect((float) $order->products_price)->toBe(90.0);

    $usage = PromoCodeUsage::where('order_id', $order->id)->firstOrFail();
    expect((float) $usage->applied_amount)->toBe(10.0);
});

it('rejects an unknown promo code', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5]);
    $this->cartService->add($this->user, null, $product->id, 1);

    $this->service->createOrder($this->user, [
        'user_name' => 'Bob',
        'user_phone' => '+380000000',
        'promo_code' => 'NOPE',
    ]);
})->throws(DomainException::class);

it('records automatic promotion usage on the order', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5]);
    $rule = DiscountRule::create(['name' => '20% off', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 20]);
    $promotion = Promotion::create(['name' => 'Promo', 'discount_rule_id' => $rule->id, 'publish' => true]);
    $this->cartService->add($this->user, null, $product->id, 1);

    $order = $this->service->createOrder($this->user, ['user_name' => 'Bob', 'user_phone' => '+380000000']);

    expect((float) $order->products_price)->toBe(80.0);

    $usage = PromotionUsage::where('order_id', $order->id)->firstOrFail();
    expect($usage->promotion_id)->toBe($promotion->id);
    expect((float) $usage->applied_amount)->toBe(20.0);
});

it('creates a quick order for a single product without a cart', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5, 'publish' => true]);

    $order = $this->service->createQuickOrder($this->user, [
        'user_name' => 'Bob',
        'user_phone' => '+380000000',
        'product_id' => $product->id,
        'quantity' => 2,
    ]);

    expect((float) $order->total_price)->toBe(200.0);
    expect($order->products()->first()->quantity)->toBe(2);
});

it('reduces stock once the order is confirmed', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5]);
    $this->cartService->add($this->user, null, $product->id, 2);
    $order = $this->service->createOrder($this->user, ['user_name' => 'Bob', 'user_phone' => '+380000000']);

    $confirmed = OrderStatus::where('slug', 'confirmed')->firstOrFail();
    $order->update(['status_id' => $confirmed->id]);

    expect($product->refresh()->stock)->toBe(3);
    expect($order->fresh()->confirmed_at)->not->toBeNull();
    expect($order->history()->count())->toBe(2);
});

it('restores stock when a confirmed order is cancelled', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5]);
    $this->cartService->add($this->user, null, $product->id, 2);
    $order = $this->service->createOrder($this->user, ['user_name' => 'Bob', 'user_phone' => '+380000000']);

    $confirmed = OrderStatus::where('slug', 'confirmed')->firstOrFail();
    $order->update(['status_id' => $confirmed->id]);
    expect($product->refresh()->stock)->toBe(3);

    $cancelled = OrderStatus::where('slug', 'cancelled')->firstOrFail();
    $order->update(['status_id' => $cancelled->id]);

    expect($product->refresh()->stock)->toBe(5);
});

it('cancels a new order via cancelOrder()', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5]);
    $this->cartService->add($this->user, null, $product->id, 1);
    $order = $this->service->createOrder($this->user, ['user_name' => 'Bob', 'user_phone' => '+380000000']);

    $this->service->cancelOrder($order);

    expect($order->fresh()->status?->slug)->toBe('cancelled');
});

it('refuses to cancel an order that is no longer new', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'stock' => 5]);
    $this->cartService->add($this->user, null, $product->id, 1);
    $order = $this->service->createOrder($this->user, ['user_name' => 'Bob', 'user_phone' => '+380000000']);

    $confirmed = OrderStatus::where('slug', 'confirmed')->firstOrFail();
    $order->update(['status_id' => $confirmed->id]);

    $this->service->cancelOrder($order);
})->throws(DomainException::class);
