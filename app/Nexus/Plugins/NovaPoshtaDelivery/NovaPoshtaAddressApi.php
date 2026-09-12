<?php

namespace App\Nexus\Plugins\NovaPoshtaDelivery;

use Illuminate\Support\Facades\Http;

/**
 * Nova Poshta's address-classifier methods — the sibling
 * Shipment\Services\Api\NovaPoshtaTrackingApi only ports the tracking
 * envelope; this ports the two calls NovaPoshtaAddressSyncer needs to cache
 * the checkout's city/warehouse picker (see DeliveryAddressSyncService's
 * docblock for why a live API call per checkout keystroke isn't done
 * instead). Same request envelope
 * (apiKey/modelName/calledMethod/methodProperties) and config keys
 * (novaposhta.base_uri/point/api_key) as NovaPoshtaTrackingApi.
 */
class NovaPoshtaAddressApi
{
    /** Max the API accepts per page for both methods below. */
    public const PAGE_LIMIT = 500;

    /**
     * One page of Ukraine's ~11k settlements. No CityRef filter — this is
     * the classifier sync, not the checkout's search-as-you-type (that's a
     * local DB query against the cached rows, see DeliveryAddressSyncService).
     *
     * @return array{data: array, totalCount: int}
     */
    public function getCitiesPage(int $page): array
    {
        $answer = $this->call('Address', 'getCities', [
            'Page' => (string) $page,
            'Limit' => (string) self::PAGE_LIMIT,
        ]);

        return [
            'data' => $answer['data'] ?? [],
            'totalCount' => (int) ($answer['info']['totalCount'] ?? 0),
        ];
    }

    /**
     * One page of ALL warehouses/postomats nationwide (no CityRef) — each
     * row carries its own CityRef/CityDescription, so paging through this
     * once is ~110 requests total instead of one getWarehouses call per one
     * of the ~11k cities.
     *
     * @return array{data: array, totalCount: int}
     */
    public function getWarehousesPage(int $page): array
    {
        $answer = $this->call('AddressGeneral', 'getWarehouses', [
            'Page' => (string) $page,
            'Limit' => (string) self::PAGE_LIMIT,
        ]);

        return [
            'data' => $answer['data'] ?? [],
            'totalCount' => (int) ($answer['info']['totalCount'] ?? 0),
        ];
    }

    private function call(string $modelName, string $calledMethod, array $methodProperties): array
    {
        $url = rtrim((string) config('novaposhta.base_uri'), '/').'/'.config('novaposhta.point').'/';

        $response = Http::timeout(30)->retry(2, 500)->acceptJson()->post($url, [
            'apiKey' => config('novaposhta.api_key'),
            'modelName' => $modelName,
            'calledMethod' => $calledMethod,
            'methodProperties' => $methodProperties,
        ]);

        if ($response->failed()) {
            throw new \RuntimeException("Nova Poshta API request failed: HTTP {$response->status()}");
        }

        $data = $response->json();

        if (empty($data['success'])) {
            $errors = implode('; ', $data['errors'] ?? ['unknown error']);
            throw new \RuntimeException("Nova Poshta API error ({$modelName}.{$calledMethod}): {$errors}");
        }

        return $data;
    }
}
