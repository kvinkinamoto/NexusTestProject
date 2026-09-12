<?php

namespace App\Nexus\Plugins\NovaPoshtaDelivery;

use App\Nexus\Modules\Delivery\Contracts\DeliveryAddressSyncerInterface;
use App\Nexus\Modules\Delivery\Models\Delivery;
use App\Nexus\Modules\Delivery\Models\DeliveryCity;
use App\Nexus\Modules\Delivery\Models\DeliveryWarehouse;

class NovaPoshtaAddressSyncer implements DeliveryAddressSyncerInterface
{
    public function __construct(private readonly NovaPoshtaAddressApi $api) {}

    public function carrierKey(): string
    {
        return 'nova-poshta';
    }

    public function sync(?\Closure $onProgress = null): array
    {
        $delivery = Delivery::where('key', $this->carrierKey())->firstOrFail();

        $citiesSynced = 0;
        $page = 1;
        do {
            $result = $this->api->getCitiesPage($page);
            $rows = array_map(fn (array $c) => [
                'delivery_id' => $delivery->id,
                'ref' => $c['Ref'],
                'name' => $c['Description'],
                'region' => $c['AreaDescription'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ], $result['data']);

            if (! empty($rows)) {
                DeliveryCity::upsert($rows, ['delivery_id', 'ref'], ['name', 'region', 'updated_at']);
                $citiesSynced += count($rows);
            }

            $onProgress && $onProgress('nova-poshta:cities', $citiesSynced, $result['totalCount']);
            $page++;
        } while (! empty($result['data']) && $citiesSynced < $result['totalCount']);

        // Carrier Ref -> local id, resolved once so every warehouse page
        // below is a single lookup instead of a query per row.
        $cityIdByRef = DeliveryCity::where('delivery_id', $delivery->id)->pluck('id', 'ref');

        $warehousesSynced = 0;
        $page = 1;
        do {
            $result = $this->api->getWarehousesPage($page);
            $rows = array_map(fn (array $w) => [
                'delivery_id' => $delivery->id,
                'delivery_city_id' => $cityIdByRef[$w['CityRef']] ?? null,
                'ref' => $w['Ref'],
                'number' => $w['Number'] ?? null,
                'name' => $w['Description'],
                'address' => $w['ShortAddress'] ?? null,
                'type' => $w['CategoryOfWarehouse'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ], $result['data']);

            if (! empty($rows)) {
                DeliveryWarehouse::upsert($rows, ['delivery_id', 'ref'], ['delivery_city_id', 'number', 'name', 'address', 'type', 'updated_at']);
                $warehousesSynced += count($rows);
            }

            $onProgress && $onProgress('nova-poshta:warehouses', $warehousesSynced, $result['totalCount']);
            $page++;
        } while (! empty($result['data']) && $warehousesSynced < $result['totalCount']);

        return ['cities' => $citiesSynced, 'warehouses' => $warehousesSynced];
    }
}
