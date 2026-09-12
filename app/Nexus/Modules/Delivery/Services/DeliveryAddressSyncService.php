<?php

namespace App\Nexus\Modules\Delivery\Services;

use App\Nexus\Modules\Delivery\Contracts\DeliveryAddressSyncerInterface;

/**
 * Carrier-agnostic on purpose — every concrete carrier (Nova Poshta,
 * Ukrposhta, a future one) plugs in via the 'delivery.address_syncers'
 * filter hook instead of being referenced here by name, so adding or
 * deleting a carrier's plugin (see DeliveryAddressSyncerInterface) never
 * touches this class. Caches into delivery_cities/delivery_warehouses so
 * the checkout's city/warehouse picker is a local DB query instead of a
 * live carrier API call per keystroke.
 */
class DeliveryAddressSyncService
{
    /**
     * @return array<string, DeliveryAddressSyncerInterface>
     */
    public function syncers(): array
    {
        $syncers = [];
        foreach (nexus_filter('delivery.address_syncers', []) as $syncer) {
            $syncers[$syncer->carrierKey()] = $syncer;
        }

        return $syncers;
    }

    /**
     * @return array{cities: int, warehouses: int}
     */
    public function sync(string $carrierKey, ?\Closure $onProgress = null): array
    {
        $syncer = $this->syncers()[$carrierKey] ?? null;
        if (! $syncer) {
            throw new \InvalidArgumentException("No address syncer registered for carrier: {$carrierKey}");
        }

        return $syncer->sync($onProgress);
    }
}
