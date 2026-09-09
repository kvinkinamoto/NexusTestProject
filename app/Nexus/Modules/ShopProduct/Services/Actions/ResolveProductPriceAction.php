<?php

namespace App\Nexus\Modules\ShopProduct\Services\Actions;

use App\Nexus\Modules\Promotion\Services\PromotionEngine;
use App\Nexus\Modules\ShopProduct\Models\ShopProduct;

class ResolveProductPriceAction
{
    /**
     * @return array{price: float, original_price: ?float, applied_promotion_id: ?int}
     */
    public static function handle(ShopProduct $product, int $quantity = 1): array
    {
        return app(PromotionEngine::class)->resolveProductPrice($product, $quantity);
    }
}
