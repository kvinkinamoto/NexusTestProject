<?php

namespace App\Nexus\Modules\ShopProduct\Services\Actions;

use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

class EnsureProductStockAction
{
    /**
     * @throws \DomainException when the requested quantity exceeds stock on hand
     */
    public static function handle(ShopProduct $product, int $quantity): void
    {
        $stock = ResolveProductStockAction::handle($product);

        if ($stock !== null && $quantity > $stock) {
            throw new \DomainException(__('shopProduct::translate.insufficient_stock'));
        }
    }
}
