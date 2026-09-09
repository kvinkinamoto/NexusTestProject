<?php

namespace App\Nexus\Modules\PromoCode\Services;

use App\Models\User;
use App\Nexus\Modules\PromoCode\Models\PromoCode;
use App\Nexus\Modules\PromoCode\Models\PromoCodeUsage;

class PromoCodeService
{
    /**
     * Deliberately not filtered by publish()/dates — an inactive/expired
     * code should still be found so validate() can raise its specific
     * error, rather than the generic "not found" one.
     */
    public function findByCode(string $code): ?PromoCode
    {
        $normalized = mb_strtoupper(trim($code));
        if ($normalized === '') {
            return null;
        }

        return PromoCode::query()->where('code', $normalized)->first();
    }

    /**
     * Throws \DomainException (same convention as CartService) on the first
     * failing check. usage_limit_per_user is only checked when $user is
     * present — a guest has no reliable identity to key that limit on.
     */
    public function validate(PromoCode $promoCode, ?User $user): void
    {
        if (! $promoCode->publish) {
            throw new \DomainException(__('promoCode::translate.error_inactive'));
        }

        $now = now();

        if ($promoCode->starts_at && $now->lt($promoCode->starts_at)) {
            throw new \DomainException(__('promoCode::translate.error_not_started'));
        }

        if ($promoCode->expires_at && $now->gt($promoCode->expires_at)) {
            throw new \DomainException(__('promoCode::translate.error_expired'));
        }

        if (! $promoCode->discount_rule_id) {
            throw new \DomainException(__('promoCode::translate.error_no_discount_rule'));
        }

        if ($promoCode->usage_limit !== null && $this->countGlobalUsage($promoCode) >= $promoCode->usage_limit) {
            throw new \DomainException(__('promoCode::translate.error_usage_limit'));
        }

        if ($user && $promoCode->usage_limit_per_user !== null
            && $this->countUserUsage($promoCode, $user) >= $promoCode->usage_limit_per_user) {
            throw new \DomainException(__('promoCode::translate.error_usage_limit_per_user'));
        }
    }

    /**
     * Non-throwing wrapper around validate() — for read-only contexts (cart
     * summary) that need to decide whether to still display an
     * already-applied code's discount, without the \DomainException control
     * flow that write-path callers (apply/checkout) use.
     */
    public function isUsable(PromoCode $promoCode, ?User $user): bool
    {
        try {
            $this->validate($promoCode, $user);

            return true;
        } catch (\DomainException) {
            return false;
        }
    }

    public function countGlobalUsage(PromoCode $promoCode): int
    {
        return PromoCodeUsage::query()->where('promo_code_id', $promoCode->id)->count();
    }

    public function countUserUsage(PromoCode $promoCode, User $user): int
    {
        return PromoCodeUsage::query()
            ->where('promo_code_id', $promoCode->id)
            ->where('user_id', $user->id)
            ->count();
    }

    /**
     * Records one consumption of the code — call this once at checkout
     * confirmation, not when it's merely applied to a cart. Nothing calls
     * this yet (Order/checkout is Phase 5); wired in from there once it
     * exists.
     */
    public function recordUsage(PromoCode $promoCode, ?User $user, float $appliedAmount): PromoCodeUsage
    {
        return PromoCodeUsage::create([
            'promo_code_id' => $promoCode->id,
            'user_id' => $user?->id,
            'applied_amount' => $appliedAmount,
        ]);
    }
}
