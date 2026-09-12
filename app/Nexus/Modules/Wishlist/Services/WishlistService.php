<?php

namespace App\Nexus\Modules\Wishlist\Services;

use App\Models\User;
use App\Nexus\Modules\Wishlist\Models\WishlistItem;

class WishlistService
{
    public function add(?User $user, ?string $sessionId, int $productId): WishlistItem
    {
        return $user
            ? $user->wishlistItems()->firstOrCreate(['product_id' => $productId])
            : WishlistItem::firstOrCreate(['session_id' => $sessionId, 'product_id' => $productId]);
    }

    public function remove(?User $user, ?string $sessionId, int $productId): int
    {
        return $this->scope($user, $sessionId)->where('product_id', $productId)->delete();
    }

    public function sync(?User $user, ?string $sessionId, array $productIds): void
    {
        if (empty($productIds)) {
            return;
        }

        $now = now();

        $rows = array_map(fn (int $productId) => [
            'user_id' => $user?->id,
            'session_id' => $user ? null : $sessionId,
            'product_id' => $productId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $productIds);

        WishlistItem::insertOrIgnore($rows);
    }

    private function scope(?User $user, ?string $sessionId)
    {
        return $user ? $user->wishlistItems() : WishlistItem::where('session_id', $sessionId);
    }
}
