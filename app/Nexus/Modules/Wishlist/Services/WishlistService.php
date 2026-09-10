<?php

namespace App\Nexus\Modules\Wishlist\Services;

use App\Models\User;
use App\Nexus\Modules\Wishlist\Models\WishlistItem;

class WishlistService
{
    public function add(User $user, int $productId): WishlistItem
    {
        return $user->wishlistItems()->firstOrCreate(['product_id' => $productId]);
    }

    public function remove(User $user, int $productId): int
    {
        return $user->wishlistItems()->where('product_id', $productId)->delete();
    }

    public function sync(User $user, array $productIds): void
    {
        if (empty($productIds)) {
            return;
        }

        $now = now();

        $rows = array_map(fn (int $productId) => [
            'user_id' => $user->id,
            'product_id' => $productId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $productIds);

        WishlistItem::insertOrIgnore($rows);
    }
}
