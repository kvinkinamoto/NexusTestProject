<?php

namespace App\Nexus\Plugins\NovaPoshtaDelivery;

use Nodex\Nexus\Attributes\Filter;
use Nodex\Nexus\Attributes\TargetModule;

/**
 * Registers NovaPoshtaAddressSyncer onto the 'delivery.address_syncers'
 * filter hook (see App\Nexus\Modules\Delivery\Services\
 * DeliveryAddressSyncService, which reads that hook instead of referencing
 * any carrier by name) — deleting this whole plugin directory removes Nova
 * Poshta's address sync cleanly, with the manager, the checkout picker, and
 * any other carrier's plugin left untouched.
 *
 * #[TargetModule('Delivery')] is a required anchor for PluginManager to
 * discover this class at all, even though nothing here mutates Delivery's
 * own admin config (see the nexus-plugins skill: a hook-only plugin still
 * needs one).
 */
#[TargetModule('Delivery')]
class NovaPoshtaDeliveryPlugin
{
    #[Filter(hook: 'delivery.address_syncers', priority: 10)]
    public function registerAddressSyncer(array $syncers): array
    {
        $syncers[] = app(NovaPoshtaAddressSyncer::class);

        return $syncers;
    }
}
