<?php

namespace App\Nexus\Modules\ShopProduct\Services\Actions;

use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

class ResolveProductStockAction
{
    public static function handle(ShopProduct $product): ?int
    {
        return $product->stock;
    }
}
