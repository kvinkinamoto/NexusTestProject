<?php

namespace App\Nexus\Modules\Cart\Services;

use App\Models\User;
use App\Nexus\Modules\Cart\Models\Cart;
use App\Nexus\Modules\PromoCode\Models\PromoCode;
use App\Nexus\Modules\PromoCode\Services\PromoCodeService;
use App\Nexus\Modules\Promotion\Services\PromotionEngine;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\ShopProduct\Services\Actions\EnsureProductStockAction;
use App\Nexus\Modules\ShopProduct\Services\Actions\ResolveProductPriceAction;
use App\Nexus\Modules\ShopProduct\Services\Actions\ResolveProductStockAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ported from ZentaraStartProject's CartService, now that Pricing rules
 * (PromoCode/DiscountRule/Promotion) exists — `calculateTotal()`/
 * `calculateBreakdown()` go through PromotionEngine for real instead of the
 * Phase 2/3 placeholder that only looked at `price_discount`, and
 * applyPromoCode()/removePromoCode() (dropped in Phase 3 pending this) are
 * back. Multi-tenant shop scoping still dropped, matching every other
 * ported module.
 */
class CartService
{
    public function add(?User $user, ?string $sessionId, int $productId, int $quantity): void
    {
        DB::transaction(function () use ($user, $sessionId, $productId, $quantity) {
            $product = $this->getProduct($productId);
            $cart = $this->resolveCart($user, $sessionId);
            $existing = $cart->products()->where('product_id', $productId)->first();
            $targetQuantity = ($existing?->quantity ?? 0) + $quantity;

            EnsureProductStockAction::handle($product, $targetQuantity);

            if ($existing) {
                $existing->update(['quantity' => $targetQuantity]);

                return;
            }

            $cart->products()->create([
                'product_id' => $productId,
                'quantity' => $quantity,
            ]);
        });
    }

    public function updateQuantity(?User $user, ?string $sessionId, int $productId, int $quantity): void
    {
        $cart = $this->findCart($user, $sessionId);
        if (! $cart) {
            throw new \DomainException(__('cart::translate.item_not_found'));
        }

        $item = $cart->products()->where('product_id', $productId)->first();
        if (! $item) {
            throw new \DomainException(__('cart::translate.item_not_found'));
        }

        if ($quantity <= 0) {
            $item->delete();

            return;
        }

        $product = $this->getProduct($productId);
        EnsureProductStockAction::handle($product, $quantity);

        $item->update(['quantity' => $quantity]);
    }

    public function remove(?User $user, ?string $sessionId, int $productId): void
    {
        $cart = $this->findCart($user, $sessionId);
        if (! $cart) {
            return;
        }

        $cart->products()->where('product_id', $productId)->delete();
    }

    public function sync(?User $user, ?string $sessionId, array $products): void
    {
        if (empty($products)) {
            return;
        }

        DB::transaction(function () use ($user, $sessionId, $products) {
            $cart = $this->resolveCart($user, $sessionId);

            $productIds = array_column($products, 'id');
            $loadedProducts = ShopProduct::query()->whereIn('id', $productIds)->get()->keyBy('id');
            $existing = $cart->products()
                ->whereIn('product_id', $productIds)
                ->get()
                ->keyBy('product_id');

            foreach ($products as $item) {
                $product = $loadedProducts->get($item['id']);
                if (! $product) {
                    continue;
                }

                $targetQuantity = max(1, (int) $item['quantity']);
                $existingItem = $existing->get($item['id']);

                $stock = ResolveProductStockAction::handle($product);
                if ($stock !== null) {
                    $targetQuantity = min($targetQuantity, $stock);
                }

                if ($targetQuantity <= 0) {
                    continue;
                }

                if ($existingItem) {
                    $existingItem->update(['quantity' => $targetQuantity]);
                } else {
                    $cart->products()->create([
                        'product_id' => $product->id,
                        'quantity' => $targetQuantity,
                    ]);
                }
            }
        });
    }

    public function calculateTotal(Collection $cartProducts, ?int $promoCodeId = null): float
    {
        return $this->calculateBreakdown($cartProducts, $promoCodeId)['total_price'];
    }

    /**
     * Same pricing as calculateTotal(), but also exposes the cart-level
     * breakdown (how much of the discount came from an applied promo code
     * specifically) for API responses that need to display it.
     */
    public function calculateBreakdown(Collection $cartProducts, ?int $promoCodeId = null): array
    {
        $subtotal = $this->lineSubtotal($cartProducts);

        $promoCodeRule = $promoCodeId
            ? PromoCode::find($promoCodeId)?->discountRule
            : null;

        $cartLevel = app(PromotionEngine::class)->applyCartLevelPromotions($cartProducts, $subtotal, $promoCodeRule, $promoCodeId);

        $promoCodeDiscount = array_sum(array_map(
            fn (array $applied) => $applied['amount'],
            array_filter($cartLevel['applied'], fn (array $applied) => $applied['promo_code_id'] !== null),
        ));

        return [
            'total_price' => (float) max(0.0, $subtotal - $cartLevel['cart_discount_total']),
            'subtotal' => $subtotal,
            'cart_discount_total' => $cartLevel['cart_discount_total'],
            'promo_code_discount' => round($promoCodeDiscount, 2),
            'free_shipping' => $cartLevel['free_shipping'],
        ];
    }

    /**
     * Loads a cart's own line items + applied promo code and computes its
     * breakdown — the convenience entry point Cart::getTotal() and
     * CartController::get() use, so callers don't have to assemble the
     * pieces themselves.
     */
    public function getBreakdownForCart(Cart $cart): array
    {
        $products = $cart->products()->with('product')->get();

        return $this->calculateBreakdown($products, $cart->promo_code_id);
    }

    private function lineSubtotal(Collection $cartProducts): float
    {
        $sum = 0.0;
        foreach ($cartProducts as $cartProduct) {
            $product = $cartProduct->product ?? null;
            if (! $product) {
                continue;
            }
            $quantity = (int) $cartProduct->quantity;
            $unit = ResolveProductPriceAction::handle($product, $quantity);
            $sum += round($unit['price'] * $quantity, 2);
        }

        return $sum;
    }

    /**
     * Resolves the cart to act on: a user's own cart when logged in,
     * otherwise the cart tied to their current (guest) session id —
     * creating either if it doesn't exist yet.
     */
    public function resolveCart(?User $user, ?string $sessionId): Cart
    {
        return $user ? $user->cart()->firstOrCreate([]) : Cart::firstOrCreate(['session_id' => $sessionId]);
    }

    /**
     * Same resolution as resolveCart(), but read-only — never creates a cart
     * just to find out it's empty.
     */
    private function findCart(?User $user, ?string $sessionId): ?Cart
    {
        return $user ? $user->cart()->first() : Cart::where('session_id', $sessionId)->first();
    }

    /**
     * Validates the code and, if it passes, persists it on the cart.
     * Re-validated again at checkout (once Order/checkout exists — Phase 5)
     * — this is just the "apply while browsing" step.
     */
    public function applyPromoCode(?User $user, ?string $sessionId, string $code): void
    {
        $promoCode = app(PromoCodeService::class)->findByCode($code);
        if (! $promoCode) {
            throw new \DomainException(__('promoCode::translate.error_not_found'));
        }

        app(PromoCodeService::class)->validate($promoCode, $user);

        $this->resolveCart($user, $sessionId)->update(['promo_code_id' => $promoCode->id]);
    }

    public function removePromoCode(?User $user, ?string $sessionId): void
    {
        $cart = $this->findCart($user, $sessionId);
        if (! $cart) {
            return;
        }

        $cart->update(['promo_code_id' => null]);
    }

    private function getProduct(int $productId): ShopProduct
    {
        $product = ShopProduct::query()->publish()->find($productId);
        if (! $product) {
            throw new \DomainException(__('cart::translate.item_not_found'));
        }

        return $product;
    }
}
