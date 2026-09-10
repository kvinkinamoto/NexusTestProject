<?php

namespace App\Nexus\Modules\Bonus\Services;

use App\Models\User;
use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use App\Nexus\Modules\Bonus\Models\BonusRule;
use App\Nexus\Modules\BonusTransaction\Enums\BonusTransactionStatusEnum;
use App\Nexus\Modules\BonusTransaction\Models\BonusTransaction;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\ProductReview\Models\Review;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * BonusRule.amount drives the awarded amount for REGISTRATION/REVIEW/MANUAL.
 * For PURCHASE, the amount is ALWAYS the caller-supplied sum of
 * ShopProduct.bonuses * quantity for the order — BonusRule(PURCHASE).amount
 * is ignored and only used to gate is_active and supply delay_days/
 * validity_days. Do not "fix" awardPurchase() to read $rule->amount.
 *
 * Every award* method returns null (rather than throwing) when no active
 * rule exists for that type — an inactive/missing rule is an expected,
 * normal configuration state, not an error, so callers never need to wrap
 * these calls in try/catch.
 */
class BonusService
{
    public function awardRegistration(User $user): ?BonusTransaction
    {
        $rule = BonusRule::forType(BonusTypeEnum::REGISTRATION->value);
        if (! $rule) {
            return null;
        }

        return $this->createTransaction($user, BonusTypeEnum::REGISTRATION, (float) $rule->amount, $rule, null, null);
    }

    public function awardPurchase(User $user, float $amount, Order $order): ?BonusTransaction
    {
        if ($amount <= 0) {
            return null;
        }

        $rule = BonusRule::forType(BonusTypeEnum::PURCHASE->value);
        if (! $rule) {
            return null;
        }

        return $this->createTransaction(
            $user, BonusTypeEnum::PURCHASE, $amount, $rule, $order,
            __('bonus::translate.description_purchase', ['order' => $order->id]),
        );
    }

    public function awardReview(User $user, Review $review): ?BonusTransaction
    {
        $rule = BonusRule::forType(BonusTypeEnum::REVIEW->value);
        if (! $rule) {
            return null;
        }

        return $this->createTransaction(
            $user, BonusTypeEnum::REVIEW, (float) $rule->amount, $rule, $review,
            __('bonus::translate.description_review', ['product' => $review->product_id]),
        );
    }

    /** Not wired to any admin UI yet — kept for parity/future manual-adjustment feature. */
    public function awardManual(User $user, float $amount, ?string $description = null): ?BonusTransaction
    {
        $rule = BonusRule::forType(BonusTypeEnum::MANUAL->value);
        if (! $rule) {
            return null;
        }

        return $this->createTransaction($user, BonusTypeEnum::MANUAL, $amount, $rule, null, $description);
    }

    /** bonuses:activate — flips PENDING -> AVAILABLE where available_at <= now(). */
    public function activatePending(): int
    {
        return BonusTransaction::query()
            ->where('status', BonusTransactionStatusEnum::PENDING)
            ->where('available_at', '<=', now())
            ->update(['status' => BonusTransactionStatusEnum::AVAILABLE]);
    }

    /** bonuses:expire — flips AVAILABLE -> EXPIRED where expires_at <= now(). */
    public function expireAvailable(): int
    {
        return BonusTransaction::query()
            ->where('status', BonusTransactionStatusEnum::AVAILABLE)
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now())
            ->update(['status' => BonusTransactionStatusEnum::EXPIRED]);
    }

    private function createTransaction(
        User $user,
        BonusTypeEnum $type,
        float $amount,
        BonusRule $rule,
        ?Model $source,
        ?string $description,
    ): BonusTransaction {
        return DB::transaction(function () use ($user, $type, $amount, $rule, $source, $description) {
            $availableAt = $rule->delay_days > 0 ? now()->addDays($rule->delay_days) : now();
            $status = $rule->delay_days > 0 ? BonusTransactionStatusEnum::PENDING : BonusTransactionStatusEnum::AVAILABLE;
            $expiresAt = $rule->validity_days ? $availableAt->copy()->addDays($rule->validity_days) : null;

            $data = [
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'status' => $status,
                'available_at' => $availableAt,
                'expires_at' => $expiresAt,
                'description' => $description,
            ];

            if ($source) {
                $data['source_type'] = $source->getMorphClass();
                $data['source_id'] = $source->getKey();
            }

            return BonusTransaction::create($data);
        });
    }
}
