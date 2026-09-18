<?php

namespace App\Nexus\Modules\Order\Services;

use App\Models\User;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use DomainException;
use Illuminate\Support\Str;

/**
 * A gift order gets a shareable code the buyer sends to their friend through
 * whatever channel they like (chat, messenger, in person) — no dependency on
 * the recipient's email or on them already having an account. Redeeming
 * just points the order's `recipient_user_id` at whoever enters the code
 * while logged in.
 */
class GiftCodeService
{
    private const LENGTH = 10;

    public function generate(): string
    {
        do {
            $code = Str::upper(Str::random(self::LENGTH));
        } while (Order::query()->where('gift_code', $code)->exists());

        return $code;
    }

    /**
     * @throws DomainException when the code doesn't exist, isn't a paid
     *                         gift, or was already redeemed by someone
     */
    public function redeem(string $code, User $user): Order
    {
        $order = Order::query()->where('gift_code', Str::upper(trim($code)))->first();

        if (! $order || ! $order->is_gift) {
            throw new DomainException(__('order::translate.gift_code_invalid'));
        }

        if ($order->payment_status !== PaymentStatus::PAID) {
            throw new DomainException(__('order::translate.gift_code_not_paid'));
        }

        if ($order->recipient_user_id !== null) {
            throw new DomainException(__('order::translate.gift_code_already_redeemed'));
        }

        $order->update(['recipient_user_id' => $user->id]);

        return $order;
    }
}
