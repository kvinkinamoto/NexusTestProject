<?php

namespace App\Nexus\Modules\Wishlist\Repositories;

use App\Models\User;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;
use Illuminate\Database\Eloquent\Collection;

class WishlistRepository
{
    public function getList(User $user): Collection
    {
        return $user->wishlistItems()->get();
    }

    public function getDetailsForUser(User $user): Collection
    {
        return ShopProduct::query()
            ->publish()
            ->whereIn('id', $user->wishlistItems()->pluck('product_id'))
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
