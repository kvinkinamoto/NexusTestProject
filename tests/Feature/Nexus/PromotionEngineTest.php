<?php

use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Enums\ScopeTarget;
use App\Nexus\Modules\DiscountRule\Enums\ValueType;
use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\Promotion\Models\Promotion;
use App\Nexus\Modules\Promotion\Services\PromotionEngine;
use App\Nexus\Modules\ShopCategory\Models\ShopCategory;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopCategory'])->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'DiscountRule'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Promotion'])->run();

    $this->engine = app(PromotionEngine::class);
});

function activatePromotion(DiscountRule $rule): Promotion
{
    return Promotion::create(['name' => 'Promo for '.$rule->name, 'discount_rule_id' => $rule->id, 'publish' => true]);
}

it('returns the base price when no rules apply', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);

    $result = $this->engine->resolveProductPrice($product);

    expect($result)->toBe(['price' => 100.0, 'original_price' => null, 'applied_promotion_id' => null]);
});

it('applies a percent product discount scoped to all products', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
    $rule = DiscountRule::create(['name' => '20% off', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 20, 'scope_target' => ScopeTarget::ALL->value]);
    $promotion = activatePromotion($rule);

    $result = $this->engine->resolveProductPrice($product);

    expect($result['price'])->toBe(80.0);
    expect($result['original_price'])->toBe(100.0);
    expect($result['applied_promotion_id'])->toBe($promotion->id);
});

it('applies a fixed-amount product discount', function () {
    $product = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100]);
    $rule = DiscountRule::create(['name' => '$15 off', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::FIXED_AMOUNT->value, 'amount' => 15, 'scope_target' => ScopeTarget::ALL->value]);
    activatePromotion($rule);

    expect($this->engine->resolveProductPrice($product)['price'])->toBe(85.0);
});

it('only discounts products in the scoped category', function () {
    $shirts = ShopCategory::create(['name' => 'Shirts', 'slug' => 'shirts']);
    $pants = ShopCategory::create(['name' => 'Pants', 'slug' => 'pants']);
    $shirt = ShopProduct::create(['name' => 'Shirt', 'slug' => 'shirt', 'price' => 100, 'category_id' => $shirts->id]);
    $jeans = ShopProduct::create(['name' => 'Jeans', 'slug' => 'jeans', 'price' => 100, 'category_id' => $pants->id]);

    $rule = DiscountRule::create(['name' => 'Shirts sale', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 50, 'scope_target' => ScopeTarget::CATEGORY->value, 'category_id' => $shirts->id]);
    activatePromotion($rule);

    expect($this->engine->resolveProductPrice($shirt)['price'])->toBe(50.0);
    expect($this->engine->resolveProductPrice($jeans)['price'])->toBe(100.0);
});

it('only discounts explicitly scoped products, and never excluded ones', function () {
    $a = ShopProduct::create(['name' => 'A', 'slug' => 'a', 'price' => 100]);
    $b = ShopProduct::create(['name' => 'B', 'slug' => 'b', 'price' => 100]);

    $rule = DiscountRule::create(['name' => 'Selected', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 50, 'scope_target' => ScopeTarget::PRODUCTS->value]);
    $rule->products()->attach([$a->id, $b->id]);
    $rule->excludedProducts()->attach($b->id);
    activatePromotion($rule);

    expect($this->engine->resolveProductPrice($a)['price'])->toBe(50.0);
    expect($this->engine->resolveProductPrice($b)['price'])->toBe(100.0);
});

it('applies quantity tier discounts based on the best matching tier', function () {
    $product = ShopProduct::create(['name' => 'Bulk item', 'slug' => 'bulk', 'price' => 100]);
    $rule = DiscountRule::create([
        'name' => 'Bulk discount', 'type' => DiscountRuleType::QUANTITY_DISCOUNT->value, 'scope_target' => ScopeTarget::ALL->value,
        'mechanics' => ['tier_rules' => [['qty_from' => 5, 'discount' => 10], ['qty_from' => 10, 'discount' => 20]]],
    ]);
    activatePromotion($rule);

    expect($this->engine->resolveProductPrice($product, 3)['price'])->toBe(100.0);
    expect($this->engine->resolveProductPrice($product, 5)['price'])->toBe(90.0);
    expect($this->engine->resolveProductPrice($product, 12)['price'])->toBe(80.0);
});

it('respects a rule min_qty gate independent of quantity tiers', function () {
    $product = ShopProduct::create(['name' => 'Item', 'slug' => 'item', 'price' => 100]);
    $rule = DiscountRule::create(['name' => 'Min qty 3', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 10, 'scope_target' => ScopeTarget::ALL->value, 'min_qty' => 3]);
    activatePromotion($rule);

    expect($this->engine->resolveProductPrice($product, 2)['price'])->toBe(100.0);
    expect($this->engine->resolveProductPrice($product, 3)['price'])->toBe(90.0);
});

it('applies buy-x-get-y as an averaged unit price across the group', function () {
    $product = ShopProduct::create(['name' => 'Item', 'slug' => 'item', 'price' => 100]);
    $rule = DiscountRule::create([
        'name' => 'Buy 2 get 1 free', 'type' => DiscountRuleType::BUY_X_GET_Y->value, 'scope_target' => ScopeTarget::ALL->value,
        'mechanics' => ['buy_qty' => 2, 'get_qty' => 1, 'get_discount_percent' => 100],
    ]);
    activatePromotion($rule);

    // below the group size: no discount
    expect($this->engine->resolveProductPrice($product, 2)['price'])->toBe(100.0);
    // one full group of 3 (2 paid + 1 free): (3*100 - 100)/3 = 66.67
    expect($this->engine->resolveProductPrice($product, 3)['price'])->toBe(66.67);
});

it('applies a flash sale up to its stock limit and stops after', function () {
    $product = ShopProduct::create(['name' => 'Doorbuster', 'slug' => 'doorbuster', 'price' => 100]);
    $rule = DiscountRule::create([
        'name' => 'Flash sale', 'type' => DiscountRuleType::FLASH_SALE->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 50, 'scope_target' => ScopeTarget::ALL->value,
        'mechanics' => ['stock_limit' => 5],
    ]);
    activatePromotion($rule);

    expect($this->engine->resolveProductPrice($product)['price'])->toBe(50.0);

    $this->engine->consumeIfFlashSale($this->engine->resolveProductPrice($product)['applied_promotion_id'], 5);

    expect($this->engine->resolveProductPrice($product)['price'])->toBe(100.0);
});

it('picks the cheapest price when several non-stackable rules apply, ignoring priority', function () {
    $product = ShopProduct::create(['name' => 'Item', 'slug' => 'item', 'price' => 100]);
    $small = DiscountRule::create(['name' => 'Small', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 10, 'scope_target' => ScopeTarget::ALL->value, 'priority' => 100]);
    $big = DiscountRule::create(['name' => 'Big', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 30, 'scope_target' => ScopeTarget::ALL->value, 'priority' => 1]);
    activatePromotion($small);
    $bigPromotion = activatePromotion($big);

    $result = $this->engine->resolveProductPrice($product);

    expect($result['price'])->toBe(70.0);
    expect($result['applied_promotion_id'])->toBe($bigPromotion->id);
});

it('multiplies stackable rules together in priority order', function () {
    $product = ShopProduct::create(['name' => 'Item', 'slug' => 'item', 'price' => 100]);
    $first = DiscountRule::create(['name' => 'First', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 10, 'scope_target' => ScopeTarget::ALL->value, 'stackable' => true, 'priority' => 10]);
    $second = DiscountRule::create(['name' => 'Second', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 10, 'scope_target' => ScopeTarget::ALL->value, 'stackable' => true, 'priority' => 1]);
    activatePromotion($first);
    activatePromotion($second);

    // 100 -> 90 (first 10%) -> 81 (second 10%)
    expect($this->engine->resolveProductPrice($product)['price'])->toBe(81.0);
});

it('lets a manual price_discount compete as a candidate against active rules', function () {
    $product = ShopProduct::create(['name' => 'Item', 'slug' => 'item', 'price' => 100, 'price_discount' => 60]);
    $rule = DiscountRule::create(['name' => 'Small', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 10, 'scope_target' => ScopeTarget::ALL->value]);
    activatePromotion($rule);

    // manual price_discount (60) beats the 10%-off rule (90)
    expect($this->engine->resolveProductPrice($product)['price'])->toBe(60.0);
});

it('does not apply a rule outside its day-of-week condition', function () {
    $product = ShopProduct::create(['name' => 'Item', 'slug' => 'item', 'price' => 100]);
    $otherDay = ((int) now()->dayOfWeek + 1) % 7;
    $rule = DiscountRule::create(['name' => 'Weekday only', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 50, 'scope_target' => ScopeTarget::ALL->value, 'day_of_week' => [$otherDay]]);
    activatePromotion($rule);

    expect($this->engine->resolveProductPrice($product)['price'])->toBe(100.0);
});

it('applies a cart threshold discount once the subtotal clears the minimum', function () {
    $rule = DiscountRule::create(['name' => 'Spend $100 save 10%', 'type' => DiscountRuleType::CART_THRESHOLD->value, 'value_type' => ValueType::PERCENT->value, 'amount' => 10, 'min_cart_amount' => 100]);
    $promotion = activatePromotion($rule);

    $below = $this->engine->applyCartLevelPromotions(collect(), 50);
    expect($below['cart_discount_total'])->toBe(0.0);

    $above = $this->engine->applyCartLevelPromotions(collect(), 150);
    expect($above['cart_discount_total'])->toBe(15.0);
    expect($above['applied'][0]['promotion_id'])->toBe($promotion->id);
});

it('flags free shipping without discounting the subtotal', function () {
    $rule = DiscountRule::create(['name' => 'Free shipping over $50', 'type' => DiscountRuleType::FREE_SHIPPING->value, 'min_cart_amount' => 50]);
    activatePromotion($rule);

    $result = $this->engine->applyCartLevelPromotions(collect(), 75);

    expect($result['free_shipping'])->toBeTrue();
    expect($result['cart_discount_total'])->toBe(0.0);
});

it('applies a bundle discount only when every bundle item is present in sufficient quantity', function () {
    $a = ShopProduct::create(['name' => 'A', 'slug' => 'a', 'price' => 30]);
    $b = ShopProduct::create(['name' => 'B', 'slug' => 'b', 'price' => 40]);
    $rule = DiscountRule::create([
        'name' => 'A+B bundle', 'type' => DiscountRuleType::BUNDLE->value,
        'mechanics' => ['bundle_items' => [['product_id' => $a->id, 'qty' => 1], ['product_id' => $b->id, 'qty' => 1]], 'bundle_price' => 60],
    ]);
    activatePromotion($rule);

    $cartProducts = collect([
        (object) ['product_id' => $a->id, 'quantity' => 1, 'product' => $a],
        (object) ['product_id' => $b->id, 'quantity' => 1, 'product' => $b],
    ]);

    $result = $this->engine->applyCartLevelPromotions($cartProducts, 70);
    expect($result['cart_discount_total'])->toBe(10.0);

    $incomplete = collect([(object) ['product_id' => $a->id, 'quantity' => 1, 'product' => $a]]);
    expect($this->engine->applyCartLevelPromotions($incomplete, 30)['cart_discount_total'])->toBe(0.0);
});

it('caps the cart discount total at the subtotal so the price never goes negative', function () {
    $rule = DiscountRule::create(['name' => 'Huge fixed discount', 'type' => DiscountRuleType::CART_THRESHOLD->value, 'value_type' => ValueType::FIXED_AMOUNT->value, 'amount' => 1000, 'min_cart_amount' => 0]);
    activatePromotion($rule);

    expect($this->engine->applyCartLevelPromotions(collect(), 50)['cart_discount_total'])->toBe(50.0);
});
