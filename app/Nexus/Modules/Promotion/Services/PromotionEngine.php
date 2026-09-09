<?php

namespace App\Nexus\Modules\Promotion\Services;

use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Enums\ScopeTarget;
use App\Nexus\Modules\DiscountRule\Enums\ValueType;
use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\Promotion\Models\Promotion;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\ShopProduct\Services\Actions\ResolveProductPriceAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Ported from ZentaraStartProject at full fidelity (only PromotionService's
 * caching layer underneath was simplified away — see that class). Implements
 * the promotions conflict-resolution algorithm: 0 matches -> base price;
 * 1 -> apply; 2+ -> split stackable/non-stackable, non-stackable resolved by
 * best-discount-wins (priority as tie-break only), stackable applied
 * multiplicatively in priority order.
 */
class PromotionEngine
{
    public function __construct(private readonly PromotionService $promotionService) {}

    /**
     * Resolve the price of a single cart/order line (one product x quantity).
     * Evaluates line-level rule types only: PRODUCT_DISCOUNT, QUANTITY_DISCOUNT, BUY_X_GET_Y, FLASH_SALE.
     */
    public function resolveProductPrice(ShopProduct $product, int $quantity = 1): array
    {
        $original = (float) ($product->price ?? 0);
        $candidates = [];

        $manualPrice = $product->price_discount !== null ? (float) $product->price_discount : null;
        if ($manualPrice !== null) {
            $candidates[] = ['price' => $manualPrice, 'promotion_id' => null];
        }

        $applicable = $this->applicableLineRules($product, $quantity);

        if (! empty($applicable)) {
            $nonStackable = array_values(array_filter($applicable, fn ($e) => ! $e['rule']->stackable));
            $stackable = array_values(array_filter($applicable, fn ($e) => $e['rule']->stackable));

            foreach ($nonStackable as $entry) {
                $price = $this->applyLineRule($entry['rule'], $original, $quantity, $product->id);
                if ($price !== null) {
                    $candidates[] = ['price' => $price, 'promotion_id' => $entry['promotion']->id];
                }
            }

            if (! empty($stackable)) {
                usort($stackable, fn ($a, $b) => $b['rule']->priority <=> $a['rule']->priority);

                $stackedPrice = $original;
                $lastPromotionId = null;
                foreach ($stackable as $entry) {
                    $result = $this->applyLineRule($entry['rule'], $stackedPrice, $quantity, $product->id);
                    if ($result !== null) {
                        $stackedPrice = $result;
                        $lastPromotionId = $entry['promotion']->id;
                    }
                }
                if ($lastPromotionId !== null) {
                    $candidates[] = ['price' => $stackedPrice, 'promotion_id' => $lastPromotionId];
                }
            }
        }

        if (empty($candidates)) {
            return ['price' => $original, 'original_price' => null, 'applied_promotion_id' => null];
        }

        usort($candidates, fn ($a, $b) => $a['price'] <=> $b['price']);
        $winner = $candidates[0];
        $price = round((float) $winner['price'], 2);

        return [
            'price' => $price,
            'original_price' => $price < $original ? $original : null,
            'applied_promotion_id' => $winner['promotion_id'],
        ];
    }

    /**
     * Resolve cart-wide promotions: CART_THRESHOLD, BUNDLE (discount amount),
     * FREE_SHIPPING (flag only — no delivery pricing seam exists yet to apply it to, see Phase 7).
     *
     * An optional promo-code rule (already validated by PromoCodeService — this method does
     * not re-check dates/usage limits) participates in the exact same best-price-wins /
     * multiplicative-stacking logic as automatic promotions, via its own stackable/priority
     * columns — it's just one more cart-level source. Each `applied[]` entry carries exactly
     * one of `promotion_id`/`promo_code_id` (the other null) so callers can attribute the
     * discount to the right log table.
     */
    public function applyCartLevelPromotions(
        Collection $cartProducts,
        float $subtotal,
        ?DiscountRule $promoCodeRule = null,
        ?int $promoCodeId = null,
    ): array {
        $result = ['cart_discount_total' => 0.0, 'free_shipping' => false, 'applied' => []];

        $sources = $this->promotionService->getCurrentlyActivePromotionsCollection()
            ->filter(fn (Promotion $p) => $p->discountRule && $p->discountRule->type->isCartLevel())
            ->map(fn (Promotion $p) => ['rule' => $p->discountRule, 'promotion_id' => $p->id, 'promo_code_id' => null])
            ->values()
            ->all();

        if ($promoCodeRule !== null && $promoCodeRule->type->isCartLevel()) {
            $sources[] = ['rule' => $promoCodeRule, 'promotion_id' => null, 'promo_code_id' => $promoCodeId];
        }

        if (empty($sources)) {
            return $result;
        }

        $candidates = [];

        foreach ($sources as $source) {
            $rule = $source['rule'];

            if (! $this->baseConditionsMatch($rule)) {
                continue;
            }

            if ($rule->type === DiscountRuleType::FREE_SHIPPING) {
                if ($rule->min_cart_amount === null || $subtotal >= (float) $rule->min_cart_amount) {
                    $result['free_shipping'] = true;
                    $result['applied'][] = ['promotion_id' => $source['promotion_id'], 'promo_code_id' => $source['promo_code_id'], 'amount' => 0.0];
                }

                continue;
            }

            $discount = match ($rule->type) {
                DiscountRuleType::CART_THRESHOLD => $this->calculateCartThresholdDiscount($rule, $subtotal),
                DiscountRuleType::BUNDLE => $this->calculateBundleDiscount($rule, $cartProducts),
                default => null,
            };

            if ($discount !== null && $discount > 0) {
                $candidates[] = [
                    'rule' => $rule,
                    'promotion_id' => $source['promotion_id'],
                    'promo_code_id' => $source['promo_code_id'],
                    'discount' => $discount,
                ];
            }
        }

        if (! empty($candidates)) {
            $nonStackable = array_values(array_filter($candidates, fn ($c) => ! $c['rule']->stackable));
            $stackable = array_values(array_filter($candidates, fn ($c) => $c['rule']->stackable));

            $total = 0.0;

            $best = null;
            foreach ($nonStackable as $c) {
                if ($best === null
                    || $c['discount'] > $best['discount']
                    || ($c['discount'] === $best['discount'] && $c['rule']->priority > $best['rule']->priority)) {
                    $best = $c;
                }
            }
            if ($best !== null) {
                $total += $best['discount'];
                $result['applied'][] = ['promotion_id' => $best['promotion_id'], 'promo_code_id' => $best['promo_code_id'], 'amount' => round($best['discount'], 2)];
            }

            foreach ($stackable as $c) {
                $total += $c['discount'];
                $result['applied'][] = ['promotion_id' => $c['promotion_id'], 'promo_code_id' => $c['promo_code_id'], 'amount' => round($c['discount'], 2)];
            }

            $result['cart_discount_total'] = round(min($total, $subtotal), 2);
        }

        return $result;
    }

    /**
     * Increments the flash-sale consumption counter for a promotion's rule (no-op for other types).
     * Called once per order line after the order is confirmed — not on price preview.
     */
    public function consumeIfFlashSale(?int $promotionId, int $quantity): void
    {
        if ($promotionId === null) {
            return;
        }

        $promotion = Promotion::query()->with('discountRule')->find($promotionId);
        $rule = $promotion?->discountRule;

        if (! $rule || $rule->type !== DiscountRuleType::FLASH_SALE) {
            return;
        }

        Cache::increment($this->flashStockCacheKey($rule->id), $quantity);
    }

    private function applicableLineRules(ShopProduct $product, int $quantity): array
    {
        $applicable = [];

        foreach ($this->promotionService->getCurrentlyActivePromotionsCollection() as $promotion) {
            $rule = $promotion->discountRule;
            if (! $rule || ! $rule->type->isLineLevel()) {
                continue;
            }

            if (! $this->productMatchesScope($rule, $product)) {
                continue;
            }

            if ($rule->min_qty && $quantity < $rule->min_qty) {
                continue;
            }

            if (! $this->baseConditionsMatch($rule)) {
                continue;
            }

            $applicable[] = ['promotion' => $promotion, 'rule' => $rule];
        }

        return $applicable;
    }

    private function productMatchesScope(DiscountRule $rule, ShopProduct $product): bool
    {
        if ($rule->excludedProducts->contains('id', $product->id)) {
            return false;
        }

        return match ($rule->scope_target) {
            ScopeTarget::ALL => true,
            ScopeTarget::CATEGORY => $rule->category_id !== null && $rule->category_id === $product->category_id,
            ScopeTarget::PRODUCTS => $rule->products->contains('id', $product->id),
        };
    }

    private function baseConditionsMatch(DiscountRule $rule): bool
    {
        if (! empty($rule->day_of_week)) {
            $today = (int) now()->dayOfWeek;
            if (! in_array($today, array_map('intval', $rule->day_of_week), true)) {
                return false;
            }
        }

        $now = now()->format('H:i:s');
        if ($rule->time_from && $now < (string) $rule->time_from) {
            return false;
        }
        if ($rule->time_to && $now > (string) $rule->time_to) {
            return false;
        }

        return true;
    }

    private function applyLineRule(DiscountRule $rule, float $basePrice, int $quantity, int $productId): ?float
    {
        return match ($rule->type) {
            DiscountRuleType::PRODUCT_DISCOUNT => $this->applyValueDiscount($rule, $basePrice, $productId),
            DiscountRuleType::QUANTITY_DISCOUNT => $this->applyQuantityTiers($rule, $basePrice, $quantity, $productId),
            DiscountRuleType::BUY_X_GET_Y => $this->applyBuyXGetY($rule, $basePrice, $quantity, $productId),
            DiscountRuleType::FLASH_SALE => $this->applyFlashSale($rule, $basePrice, $productId),
            default => null,
        };
    }

    /**
     * mechanics.product_prices (a plain JSON array on the rule, e.g.
     * [{"product_id":5,"price":9.99}]) lets a rule assign an explicit final
     * price to specific products. It only replaces *which price* is charged
     * once the type's own eligibility gate already says the rule applies
     * (tier reached, stock available, a full buy/get group present) — it
     * never bypasses that gate. Each call site below checks this only after
     * its own gate has already passed.
     */
    private function findProductPriceOverride(DiscountRule $rule, int $productId): ?float
    {
        $overrides = (array) ($rule->mechanics['product_prices'] ?? []);
        foreach ($overrides as $row) {
            if ((int) ($row['product_id'] ?? 0) === $productId) {
                return isset($row['price']) && $row['price'] !== '' ? max(0.0, (float) $row['price']) : null;
            }
        }

        return null;
    }

    private function applyValueDiscount(DiscountRule $rule, float $basePrice, ?int $productId = null): ?float
    {
        if ($productId !== null && ($override = $this->findProductPriceOverride($rule, $productId)) !== null) {
            return $override;
        }

        if ($rule->amount === null) {
            return null;
        }

        $amount = (float) $rule->amount;

        if ($rule->value_type === ValueType::FIXED_AMOUNT) {
            return max(0.0, $basePrice - $amount);
        }

        return max(0.0, $basePrice * (1 - $amount / 100));
    }

    private function applyQuantityTiers(DiscountRule $rule, float $basePrice, int $quantity, ?int $productId = null): ?float
    {
        $tiers = (array) ($rule->mechanics['tier_rules'] ?? []);
        if (empty($tiers)) {
            return null;
        }

        $best = null;
        foreach ($tiers as $tier) {
            $qtyFrom = (int) ($tier['qty_from'] ?? 0);
            if ($quantity >= $qtyFrom && ($best === null || $qtyFrom > (int) $best['qty_from'])) {
                $best = $tier;
                $best['qty_from'] = $qtyFrom;
            }
        }

        if ($best === null) {
            return null;
        }

        if ($productId !== null && ($override = $this->findProductPriceOverride($rule, $productId)) !== null) {
            return $override;
        }

        $discount = (float) ($best['discount'] ?? 0);

        return max(0.0, $basePrice * (1 - $discount / 100));
    }

    private function applyBuyXGetY(DiscountRule $rule, float $basePrice, int $quantity, ?int $productId = null): ?float
    {
        $mechanics = $rule->mechanics ?? [];
        $buyQty = (int) ($mechanics['buy_qty'] ?? 0);
        $getQty = (int) ($mechanics['get_qty'] ?? 0);
        $discountPercent = (float) ($mechanics['get_discount_percent'] ?? 100);
        $groupSize = $buyQty + $getQty;

        if ($groupSize <= 0 || $quantity < $groupSize) {
            return null;
        }

        if ($productId !== null && ($override = $this->findProductPriceOverride($rule, $productId)) !== null) {
            return $override;
        }

        $groups = intdiv($quantity, $groupSize);
        $freeUnits = $groups * $getQty;
        $totalPrice = ($quantity * $basePrice) - ($freeUnits * $basePrice * $discountPercent / 100);

        return max(0.0, $totalPrice / $quantity);
    }

    private function applyFlashSale(DiscountRule $rule, float $basePrice, ?int $productId = null): ?float
    {
        $stockLimit = $rule->mechanics['stock_limit'] ?? null;
        if ($stockLimit !== null && $stockLimit !== '') {
            $consumed = (int) Cache::get($this->flashStockCacheKey($rule->id), 0);
            if ($consumed >= (int) $stockLimit) {
                return null;
            }
        }

        return $this->applyValueDiscount($rule, $basePrice, $productId);
    }

    private function calculateCartThresholdDiscount(DiscountRule $rule, float $subtotal): ?float
    {
        if ($rule->min_cart_amount !== null && $subtotal < (float) $rule->min_cart_amount) {
            return null;
        }

        if ($rule->amount === null) {
            return null;
        }

        $amount = (float) $rule->amount;

        if ($rule->value_type === ValueType::FIXED_AMOUNT) {
            return min($amount, $subtotal);
        }

        return round($subtotal * ($amount / 100), 2);
    }

    private function calculateBundleDiscount(DiscountRule $rule, Collection $cartProducts): ?float
    {
        $items = (array) ($rule->mechanics['bundle_items'] ?? []);
        $bundlePrice = $rule->mechanics['bundle_price'] ?? null;

        if (empty($items) || $bundlePrice === null || $bundlePrice === '') {
            return null;
        }

        $qtyByProduct = $cartProducts->mapWithKeys(fn ($cp) => [(int) $cp->product_id => (int) $cp->quantity]);

        $originalTotal = 0.0;
        foreach ($items as $item) {
            $productId = (int) ($item['product_id'] ?? 0);
            $requiredQty = (int) ($item['qty'] ?? 1);

            if ($qtyByProduct->get($productId, 0) < $requiredQty) {
                return null;
            }

            $cartProduct = $cartProducts->first(fn ($cp) => (int) $cp->product_id === $productId);
            if (! $cartProduct || ! $cartProduct->product) {
                return null;
            }

            $unitPriceData = ResolveProductPriceAction::handle($cartProduct->product, $requiredQty);
            $originalTotal += (float) $unitPriceData['price'] * $requiredQty;
        }

        $discount = $originalTotal - (float) $bundlePrice;

        return $discount > 0 ? round($discount, 2) : null;
    }

    private function flashStockCacheKey(int $discountRuleId): string
    {
        return "promotion:flash_stock_consumed:{$discountRuleId}";
    }
}
