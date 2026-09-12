<?php

namespace App\Nexus\Modules\Delivery\Contracts;

/**
 * One implementation per carrier, each shipped as its own
 * app/Nexus/Plugins/{Carrier}Delivery/ plugin registered on the
 * 'delivery.address_syncers' filter hook (see DeliveryAddressSyncService,
 * which never references a carrier by name) — deleting a carrier's plugin
 * directory removes that carrier's address sync entirely and leaves
 * everything else (the manager, the delivery_cities/delivery_warehouses
 * tables, the checkout picker, the other carrier) working untouched.
 */
interface DeliveryAddressSyncerInterface
{
    /**
     * Must match a `deliveries.key` row (Order.delivery_method) — the
     * carrier this syncer's data is filed under.
     */
    public function carrierKey(): string;

    /**
     * Fetch and upsert this carrier's cities/warehouses. $onProgress, if
     * given, is called as (string $stage, int $done, ?int $total) for a
     * console progress line — $total may be null when the source API
     * doesn't expose one.
     *
     * @return array{cities: int, warehouses: int}
     */
    public function sync(?\Closure $onProgress = null): array;
}
