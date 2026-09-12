<?php

namespace App\Nexus\Plugins\UkrPoshtaDelivery;

use App\Nexus\Modules\Delivery\Contracts\DeliveryAddressSyncerInterface;
use App\Nexus\Modules\Delivery\Models\Delivery;
use App\Nexus\Modules\Delivery\Models\DeliveryCity;
use App\Nexus\Modules\Delivery\Models\DeliveryWarehouse;

class UkrPoshtaAddressSyncer implements DeliveryAddressSyncerInterface
{
    public function __construct(private readonly UkrPoshtaAddressApi $api) {}

    public function carrierKey(): string
    {
        return 'ukr-poshta';
    }

    public function sync(?\Closure $onProgress = null): array
    {
        $delivery = Delivery::where('key', $this->carrierKey())->firstOrFail();

        $warehousesSynced = 0;
        $cityRefsSeen = [];

        foreach ($this->api->getRegions() as $region) {
            $offices = $this->api->getPostOfficesByRegion($region['id']);
            if (empty($offices)) {
                continue;
            }

            // Post offices double as this carrier's only source of city
            // names — upsert the distinct cities this region's batch
            // touches before the offices that reference them.
            $cityRows = [];
            foreach ($offices as $office) {
                if ($office['cityRef'] === '' || isset($cityRefsSeen[$office['cityRef']])) {
                    continue;
                }
                $cityRefsSeen[$office['cityRef']] = true;
                $cityRows[] = [
                    'delivery_id' => $delivery->id,
                    'ref' => $office['cityRef'],
                    'name' => $office['cityName'] ?: $office['cityRef'],
                    'region' => $region['name'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            if (! empty($cityRows)) {
                DeliveryCity::upsert($cityRows, ['delivery_id', 'ref'], ['name', 'region', 'updated_at']);
            }

            $cityIdByRef = DeliveryCity::where('delivery_id', $delivery->id)
                ->whereIn('ref', array_unique(array_column($offices, 'cityRef')))
                ->pluck('id', 'ref');

            $warehouseRows = array_map(fn (array $o) => [
                'delivery_id' => $delivery->id,
                'delivery_city_id' => $cityIdByRef[$o['cityRef']] ?? null,
                'ref' => $o['ref'],
                'number' => $o['number'],
                'name' => $o['name'],
                'address' => $o['address'],
                'type' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ], $offices);

            DeliveryWarehouse::upsert($warehouseRows, ['delivery_id', 'ref'], ['delivery_city_id', 'number', 'name', 'address', 'updated_at']);
            $warehousesSynced += count($warehouseRows);

            $onProgress && $onProgress('ukr-poshta:region:'.$region['name'], $warehousesSynced, null);
        }

        return ['cities' => count($cityRefsSeen), 'warehouses' => $warehousesSynced];
    }
}
