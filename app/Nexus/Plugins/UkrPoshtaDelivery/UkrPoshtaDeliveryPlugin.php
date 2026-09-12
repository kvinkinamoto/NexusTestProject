<?php

namespace App\Nexus\Plugins\UkrPoshtaDelivery;

use Nodex\Nexus\Attributes\Filter;
use Nodex\Nexus\Attributes\TargetModule;

/**
 * Registers UkrPoshtaAddressSyncer onto the 'delivery.address_syncers'
 * filter hook — see App\Nexus\Plugins\NovaPoshtaDelivery\
 * NovaPoshtaDeliveryPlugin's docblock for the full rationale (same pattern,
 * independent plugin — deleting this directory removes Ukrposhta's address
 * sync cleanly without touching Nova Poshta's or the shared manager).
 */
#[TargetModule('Delivery')]
class UkrPoshtaDeliveryPlugin
{
    #[Filter(hook: 'delivery.address_syncers', priority: 10)]
    public function registerAddressSyncer(array $syncers): array
    {
        $syncers[] = app(UkrPoshtaAddressSyncer::class);

        return $syncers;
    }
}
