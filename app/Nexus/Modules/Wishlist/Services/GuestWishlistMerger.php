<?php

namespace App\Nexus\Modules\Wishlist\Services;

use App\Models\User;
use App\Nexus\Modules\Wishlist\Models\WishlistItem;

/**
 * Called explicitly right after a successful login/register/social-login —
 * see App\Nexus\Modules\Cart\Services\GuestCartMerger's docblock for why
 * this can't be a Login event listener.
 */
class GuestWishlistMerger
{
    public function merge(User $user, ?string $guestSessionId): void
    {
        if (! $guestSessionId) {
            return;
        }

        $productIds = WishlistItem::where('session_id', $guestSessionId)->pluck('product_id');
        if ($productIds->isEmpty()) {
            return;
        }

        $now = now();
        $rows = $productIds->map(fn (int $productId) => [
            'user_id' => $user->id,
            'product_id' => $productId,
            'created_at' => $now,
            'updated_at' => $now,
        ])->all();

        WishlistItem::insertOrIgnore($rows);
        WishlistItem::where('session_id', $guestSessionId)->delete();
    }
}
