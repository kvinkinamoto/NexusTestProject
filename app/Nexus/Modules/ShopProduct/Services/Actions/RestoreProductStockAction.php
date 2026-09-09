<?php

namespace App\Nexus\Modules\ShopProduct\Services\Actions;

use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

class RestoreProductStockAction
{
    public static function handle(ShopProduct $product, int $quantity): void
    {
        if ($product->stock === null) {
            return;
        }

        $product->stock += $quantity;
        $product->save();
    }
}
