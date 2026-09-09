<?php

use App\Models\User;
use App\Nexus\Modules\Cart\Services\CartService;
use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Enums\ValueType;
use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\PromoCode\Models\PromoCode;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'DiscountRule'])->run();
    $this->artisan('nexus:module:install', ['name' => 'PromoCode'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Cart'])->run();

    $this->service = app(CartService::class);
    $this->user = User::factory()->create();
    $this->rule = DiscountRule::create(['name' => '10% cart', 'type' => DiscountRuleType::CART_THRESHOLD->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 10]);
    $this->code = PromoCode::create(['code' => 'SAVE10', 'discount_rule_id' => $this->rule->id]);
});

it('applies a valid promo code to the cart', function () {
    $this->service->applyPromoCode($this->user, 'save10');

    expect($this->service->getCart($this->user)->promo_code_id)->toBe($this->code->id);
});

it('rejects applying an unknown promo code', function () {
    $this->service->applyPromoCode($this->user, 'NOPE');
})->throws(DomainException::class);

it('rejects applying an inactive promo code', function () {
    $this->code->update(['publish' => false]);

    $this->service->applyPromoCode($this->user, 'SAVE10');
})->throws(DomainException::class);

it('removes the promo code from the cart', function () {
    $this->service->applyPromoCode($this->user, 'SAVE10');
    $this->service->removePromoCode($this->user);

    expect($this->service->getCart($this->user)->promo_code_id)->toBeNull();
});

it('reflects the applied promo code discount in the cart breakdown', function () {
    $product = ShopProduct::create(['name' => 'Item', 'slug' => 'item', 'price' => 100]);
    $this->service->add($this->user, $product->id, 1);
    $this->service->applyPromoCode($this->user, 'SAVE10');

    $cart = $this->service->getCart($this->user);
    $breakdown = $this->service->getBreakdownForCart($cart);

    expect($breakdown['subtotal'])->toBe(100.0);
    expect($breakdown['cart_discount_total'])->toBe(10.0);
    expect($breakdown['promo_code_discount'])->toBe(10.0);
    expect($breakdown['total_price'])->toBe(90.0);
});

it('leaves the total unaffected once the promo code is removed', function () {
    $product = ShopProduct::create(['name' => 'Item', 'slug' => 'item', 'price' => 100]);
    $this->service->add($this->user, $product->id, 1);
    $this->service->applyPromoCode($this->user, 'SAVE10');
    $this->service->removePromoCode($this->user);

    $cart = $this->service->getCart($this->user);
    expect($this->service->getBreakdownForCart($cart)['total_price'])->toBe(100.0);
});
