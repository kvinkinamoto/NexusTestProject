<?php

namespace App\Nexus\Modules\Promotion\Services;

use App\Nexus\Modules\Promotion\Models\Promotion;
use Illuminate\Support\Collection;

/**
 * Simplified from ZentaraStartProject: no rememberForever() caching layer
 * (and so no cache-invalidating Store/Update/Delete/BoolToggle hooks either)
 * — just a live query. A straightforward place to add caching back later if
 * a real install's promotion count ever makes this a bottleneck.
 */
class PromotionService
{
    public function getCurrentlyActivePromotionsCollection(): Collection
    {
        return Promotion::query()
            ->publish()
            ->with(['discountRule.category', 'discountRule.products', 'discountRule.excludedProducts'])
            ->whereHas('discountRule')
            ->get()
            ->filter(fn (Promotion $promotion) => $promotion->isCurrentlyActive())
            ->values();
    }
}
