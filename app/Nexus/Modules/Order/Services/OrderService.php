<?php

namespace App\Nexus\Modules\Order\Services;

use App\Models\User;
use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use App\Nexus\Modules\Bonus\Services\BonusService;
use App\Nexus\Modules\BonusTransaction\Models\BonusTransaction;
use App\Nexus\Modules\Cart\Models\Cart;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use App\Nexus\Modules\PromoCode\Models\PromoCode;
use App\Nexus\Modules\PromoCode\Models\PromoCodeUsage;
use App\Nexus\Modules\PromoCode\Services\PromoCodeService;
use App\Nexus\Modules\Promotion\Models\PromotionUsage;
use App\Nexus\Modules\Promotion\Services\PromotionEngine;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\ShopProduct\Services\Actions\EnsureProductStockAction;
use App\Nexus\Modules\ShopProduct\Services\Actions\ResolveProductPriceAction;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Nodex\Nexus\Services\SettingsBuilder;

/**
 * Ported from ZentaraStartProject at full fidelity for the checkout math
 * (promotion/promo-code resolution, stock reservation, usage logging,
 * purchase-bonus awarding). The only real cut is the CartRepository
 * indirection, inlined here as resolveOrderLines() since there's now only
 * one caller. transformOrderData() also generates a gift_code via
 * GiftCodeService whenever $data['is_gift'] is truthy — see
 * GiftCodeService::redeem() for how a recipient later claims the order.
 */
class OrderService
{
    public function __construct(
        private readonly PromotionEngine $promotionEngine,
        private readonly PromoCodeService $promoCodeService,
        private readonly BonusService $bonusService,
        private readonly GiftCodeService $giftCodeService,
    ) {}

    public function createOrder(?User $user, array $data, ?string $guestSessionId = null): Order
    {
        $allowGuest = SettingsBuilder::get('order', 'allow_guest_checkout', true);
        if (! $user && ! $allowGuest) {
            throw new \DomainException(__('order::translate.auth_required'));
        }

        return DB::transaction(function () use ($user, $data, $guestSessionId) {
            $orderLines = $this->resolveOrderLines($user, $guestSessionId, $data);

            if ($orderLines->isEmpty()) {
                throw new \DomainException(__('order::translate.cart_empty'));
            }

            $built = $this->buildOrderProductsData($orderLines);
            $orderProductsData = $built['rows'];
            $lineSubtotal = array_sum(array_column($orderProductsData, 'total_price'));

            $promoCode = $this->resolvePromoCode($user, $guestSessionId, $data);
            if ($promoCode) {
                $this->promoCodeService->validate($promoCode, $user);
            }

            $cartLevel = $this->promotionEngine->applyCartLevelPromotions(
                $orderLines,
                $lineSubtotal,
                $promoCode?->discountRule,
                $promoCode?->id,
            );

            $productsPrice = (float) max(0.0, $lineSubtotal - $cartLevel['cart_discount_total']);
            $deliveryPrice = null;
            $totalPrice = (float) $productsPrice + (float) ($deliveryPrice ?? 0);

            $orderData = $this->transformOrderData($user, $data, [
                'products_price' => $productsPrice,
                'delivery_price' => $deliveryPrice,
                'total_price' => $totalPrice,
                'promo_code_id' => $promoCode?->id,
            ]);

            $order = Order::create($orderData);

            foreach ($orderProductsData as $row) {
                $order->products()->create($row);
            }

            $this->recordPromotionUsage($user, $order, $built['promotions'], $cartLevel['applied']);

            $cart = $user ? $user->cart()->first() : Cart::where('session_id', $guestSessionId)->first();
            $cart?->products()->delete();
            $cart?->update(['promo_code_id' => null]);

            return $order;
        });
    }

    public function createQuickOrder(?User $user, array $data): Order
    {
        return DB::transaction(function () use ($user, $data) {
            $product = ShopProduct::query()->publish()->findOrFail($data['product_id']);
            $quantity = (int) ($data['quantity'] ?? 1);

            $built = $this->buildOrderProductData($product, $quantity);
            $orderProductData = $built['row'];
            $productsPrice = (float) $orderProductData['total_price'];

            $orderData = $this->transformOrderData($user, $data, [
                'products_price' => $productsPrice,
                'delivery_price' => null,
                'total_price' => $productsPrice,
            ]);

            $order = Order::create($orderData);
            $order->products()->create($orderProductData);

            $promotions = $built['promotion'] !== null ? [$built['promotion']] : [];
            $this->recordPromotionUsage($user, $order, $promotions, []);

            return $order;
        });
    }

    public function cancelOrder(Order $order): void
    {
        if ($order->status?->slug !== 'new') {
            throw new \DomainException(__('order::translate.cannot_cancel'));
        }

        $cancelledStatus = OrderStatus::query()
            ->publish()
            ->where('slug', 'cancelled')
            ->firstOrFail();

        $order->status_id = $cancelledStatus->id;
        $order->save();
    }

    /**
     * Authed users and guests with a session-scoped Cart (see
     * App\Nexus\Modules\Cart\Services\CartService) both read their persisted
     * cart the same way; only a guest with *no* cart row at all falls back to
     * resolving straight from a posted `products` array (id/quantity pairs) —
     * e.g. the "buy now" style flow with nothing ever added to a cart. Every
     * path returns a uniform collection of objects exposing
     * `->product`/`->quantity`, so buildOrderProductsData() doesn't need to
     * know which path produced them.
     */
    private function resolveOrderLines(?User $user, ?string $guestSessionId, array $data): Collection
    {
        $cart = $user ? $user->cart()->first() : Cart::where('session_id', $guestSessionId)->first();
        if ($cart) {
            $lines = $cart->products()->with('product')->get();
            if ($lines->isNotEmpty()) {
                return $lines;
            }
        }

        $requested = collect($data['products'] ?? [])->keyBy('id');
        if ($requested->isEmpty()) {
            return collect();
        }

        $products = ShopProduct::query()->publish()->whereIn('id', $requested->keys())->get()->keyBy('id');

        return $requested
            ->map(function (array $row, int $id) use ($products) {
                $product = $products->get($id);

                return $product ? (object) ['product' => $product, 'quantity' => max(1, (int) $row['quantity'])] : null;
            })
            ->filter()
            ->values();
    }

    /**
     * $data['promo_code'] takes precedence (works for guests, and lets an authed request
     * override) — otherwise falls back to the persisted cart's own selection (authed user's
     * or guest's session-scoped cart, same resolution as resolveOrderLines()). Locked
     * (lockForUpdate, inside the caller's transaction) so two concurrent orders can't both
     * slip past a usage_limit of 1. No ->publish() filter here — an unpublished/expired code
     * should still be found so PromoCodeService::validate() can raise its specific error,
     * rather than the generic "not found" one.
     */
    private function resolvePromoCode(?User $user, ?string $guestSessionId, array $data): ?PromoCode
    {
        $code = $data['promo_code'] ?? null;

        if ($code) {
            $normalized = mb_strtoupper(trim((string) $code));
            $promoCode = PromoCode::query()->lockForUpdate()->where('code', $normalized)->first();
            if (! $promoCode) {
                throw new \DomainException(__('promoCode::translate.error_not_found'));
            }

            return $promoCode;
        }

        $cart = $user ? $user->cart()->first() : Cart::where('session_id', $guestSessionId)->first();
        $cartPromoCodeId = $cart?->promo_code_id;

        return $cartPromoCodeId ? PromoCode::query()->lockForUpdate()->find($cartPromoCodeId) : null;
    }

    private function buildOrderProductsData(Collection $orderLines): array
    {
        $rows = [];
        $promotions = [];

        foreach ($orderLines as $line) {
            $product = $line->product;
            if (! $product) {
                continue;
            }

            $built = $this->buildOrderProductData($product, (int) $line->quantity);
            $rows[] = $built['row'];
            if ($built['promotion'] !== null) {
                $promotions[] = $built['promotion'];
            }
        }

        return ['rows' => $rows, 'promotions' => $promotions];
    }

    private function buildOrderProductData(ShopProduct $product, int $quantity): array
    {
        EnsureProductStockAction::handle($product, $quantity);

        $priceData = ResolveProductPriceAction::handle($product, $quantity);
        $price = (float) $priceData['price'];

        $promotion = null;
        if (! empty($priceData['applied_promotion_id']) && ! empty($priceData['original_price'])) {
            $promotion = [
                'promotion_id' => $priceData['applied_promotion_id'],
                'amount' => round(((float) $priceData['original_price'] - $price) * $quantity, 2),
                'quantity' => $quantity,
            ];
        }

        return [
            'row' => [
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
                'total_price' => round($price * $quantity, 2),
                'product_data' => $this->buildProductData($product, $priceData),
            ],
            'promotion' => $promotion,
        ];
    }

    private function buildProductData(ShopProduct $product, array $priceData): array
    {
        $nameTranslations = $product->getTranslations('name');

        return [
            'id' => $product->id,
            'name' => $nameTranslations !== [] ? $nameTranslations : $product->name,
            'slug' => $product->slug,
            'image' => $product->image,
            'price' => $priceData['price'],
            'original_price' => $priceData['original_price'] ?? null,
        ];
    }

    /**
     * Persists PromotionUsage/PromoCodeUsage rows (one per distinct promotion/code that
     * contributed a non-zero discount to this order) and, for FLASH_SALE rules, decrements the
     * remaining stock counter. Called only on confirmed order creation, never on price preview.
     * cartApplications entries carry exactly one of promotion_id/promo_code_id (see
     * PromotionEngine::applyCartLevelPromotions()) — branch on which is set to write to the
     * right log table. lineApplications are always promotion-sourced (promo codes are
     * cart-level only, never applied per line).
     */
    private function recordPromotionUsage(?User $user, Order $order, array $lineApplications, array $cartApplications): void
    {
        $promotionAmounts = [];
        $promoCodeAmounts = [];

        foreach ($lineApplications as $application) {
            $promotionId = $application['promotion_id'];
            $promotionAmounts[$promotionId] = ($promotionAmounts[$promotionId] ?? 0) + $application['amount'];
        }

        foreach ($cartApplications as $application) {
            if (! empty($application['promo_code_id'])) {
                $promoCodeId = $application['promo_code_id'];
                $promoCodeAmounts[$promoCodeId] = ($promoCodeAmounts[$promoCodeId] ?? 0) + $application['amount'];

                continue;
            }

            $promotionId = $application['promotion_id'];
            $promotionAmounts[$promotionId] = ($promotionAmounts[$promotionId] ?? 0) + $application['amount'];
        }

        foreach ($promotionAmounts as $promotionId => $amount) {
            PromotionUsage::create([
                'promotion_id' => $promotionId,
                'user_id' => $user?->id,
                'order_id' => $order->id,
                'applied_amount' => round((float) $amount, 2),
            ]);
        }

        foreach ($promoCodeAmounts as $promoCodeId => $amount) {
            PromoCodeUsage::create([
                'promo_code_id' => $promoCodeId,
                'user_id' => $user?->id,
                'order_id' => $order->id,
                'applied_amount' => round((float) $amount, 2),
            ]);
        }

        foreach ($lineApplications as $application) {
            $this->promotionEngine->consumeIfFlashSale($application['promotion_id'], $application['quantity']);
        }
    }

    private function transformOrderData(?User $user, array $data, array $totals): array
    {
        return [
            'user_id' => $user?->id,
            'promo_code_id' => $totals['promo_code_id'] ?? null,
            'status_id' => OrderStatus::query()->default()->first()?->id,

            'user_name' => $data['user_name'] ?? null,
            'user_last_name' => $data['user_last_name'] ?? null,
            'user_phone' => $data['user_phone'] ?? null,
            'user_email' => $data['user_email'] ?? null,
            'user_comment' => $data['user_comment'] ?? null,
            'needs_phone_call' => $data['needs_phone_call'] ?? true,

            'is_another_recipient' => $data['is_another_recipient'] ?? false,
            'recipient_name' => $data['recipient_name'] ?? null,
            'recipient_last_name' => $data['recipient_last_name'] ?? null,
            'recipient_phone' => $data['recipient_phone'] ?? null,

            'delivery_method' => $data['delivery_method'] ?? null,
            'delivery_type' => $data['delivery_type'] ?? null,
            'delivery_city' => $data['delivery_city'] ?? null,
            'delivery_address' => $data['delivery_address'] ?? null,

            'is_gift' => $data['is_gift'] ?? false,
            'gift_recipient_email' => $data['gift_recipient_email'] ?? null,
            'gift_message' => $data['gift_message'] ?? null,
            'gift_code' => ! empty($data['is_gift']) ? $this->giftCodeService->generate() : null,
            'recipient_user_id' => null,

            'payment_method' => $data['payment_method'] ?? null,
            'payment_status' => PaymentStatus::PENDING,

            'products_price' => $totals['products_price'],
            'delivery_price' => $totals['delivery_price'],
            'total_price' => $totals['total_price'],
            'language_code' => app()->getLocale(),

            'ip' => request()->ip(),
            'forwarded_ip' => request()->getClientIp(),
        ];
    }

    /**
     * Called from OrderObserver::updated() the moment completed_at is first
     * set (order status transitions to "delivered"). Awards a fixed amount
     * per product (ShopProduct.bonuses * quantity, summed across order
     * lines) — a PURCHASE BonusRule still has to be active to gate the
     * award, but its own `amount` is never used here. Idempotent: a repeated
     * save of an already-bonused order is a no-op.
     */
    public function addOrderBonuses(Order $order): ?BonusTransaction
    {
        if (! $order->user_id) {
            return null;
        }

        $alreadyAwarded = BonusTransaction::query()
            ->where('type', BonusTypeEnum::PURCHASE)
            ->where('source_type', (new Order)->getMorphClass())
            ->where('source_id', $order->id)
            ->exists();
        if ($alreadyAwarded) {
            return null;
        }

        $order->loadMissing('products.product', 'user');
        $amount = (float) $order->products->sum(
            fn ($orderProduct) => $orderProduct->product?->bonuses
                ? (float) $orderProduct->product->bonuses * (int) $orderProduct->quantity
                : 0.0
        );
        if ($amount <= 0) {
            return null;
        }

        return $this->bonusService->awardPurchase($order->user, $amount, $order);
    }
}
