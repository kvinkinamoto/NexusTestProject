<?php

namespace App\Nexus\Modules\Wishlist\Repositories;

use App\Models\User;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use App\Nexus\Modules\Wishlist\Models\WishlistItem;
use Illuminate\Database\Eloquent\Collection;

class WishlistRepository
{
    public function getList(?User $user, ?string $sessionId = null): Collection
    {
        return $user
            ? $user->wishlistItems()->get()
            : WishlistItem::where('session_id', $sessionId)->get();
    }

    public function getDetailsForUser(?User $user, ?string $sessionId = null): Collection
    {
        $productIds = $user
            ? $user->wishlistItems()->pluck('product_id')
            : WishlistItem::where('session_id', $sessionId)->pluck('product_id');

        return ShopProduct::query()
            ->publish()
            ->whereIn('id', $productIds)
            ->get();
    }

    public function resolveItems(array $productIds): Collection
    {
        return ShopProduct::query()
            ->publish()
            ->whereIn('id', $productIds)
            ->get();
    }
}
