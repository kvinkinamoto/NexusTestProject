<?php

namespace App\Nexus\Plugins\UkrPoshtaDelivery;

use Illuminate\Support\Facades\Http;

/**
 * Ukrposhta's address-classifier-ws — a completely separate API/credential
 * from Shipment\Services\Api\UkrPoshtaTrackingApi's Status Tracking service
 * (see config/ukrposhta.php's docblock: Ukrposhta issues a distinct
 * "Counterparty" bearer for this one, manager-issued after signing the
 * address-classifier agreement, not self-serve like Nova Poshta's key — set
 * it as UP_ADDRESS_CLASSIFIER_BEARER once obtained). GET +
 * `Authorization: Bearer` auth, `{"Entries":{"Entry":[...]}}` response
 * envelope — verified against the address-classifier-ws endpoint constants
 * and entity field mappings in tibezh/ukrposhta-php-sdk (MIT), since
 * Ukrposhta's own PDF docs aren't machine-readable and we have no bearer
 * yet to test against directly.
 */
class UkrPoshtaAddressApi
{
    /**
     * All regions (oblasts) — no useful pagination documented, ~25 rows.
     *
     * @return array<int, array{id: int, name: string}>
     */
    public function getRegions(): array
    {
        $entries = $this->call('/get_regions_by_region_ua', ['region_name_ua' => '']);

        return array_map(fn (array $e) => [
            'id' => (int) $e['REGION_ID'],
            'name' => (string) ($e['REGION_UA'] ?? ''),
        ], $entries);
    }

    /**
     * Every post office in one region in a single call — cheaper than
     * walking district->city->post-office for each of a region's cities.
     * Each row already carries its own city id/name, which is all the
     * checkout picker needs.
     *
     * @return array<int, array{ref: string, cityRef: string, cityName: string, number: string|null, name: string, address: string|null}>
     */
    public function getPostOfficesByRegion(int $regionId): array
    {
        $entries = $this->call('/get_postoffices_by_postindex', ['poRegionId' => $regionId]);

        return array_map(fn (array $e) => [
            'ref' => (string) $e['ID'],
            'cityRef' => (string) ($e['POCITY_ID'] ?? ''),
            'cityName' => (string) ($e['PDCITY_UA'] ?? $e['CITYTYPE_UA'] ?? ''),
            'number' => isset($e['MEREZA_NUMBER']) ? (string) $e['MEREZA_NUMBER'] : null,
            'name' => (string) ($e['PO_LONG'] ?? $e['PO_SHORT'] ?? ''),
            'address' => $e['ADDRESS'] ?? null,
        ], $entries);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function call(string $endpoint, array $query): array
    {
        $bearer = config('ukrposhta.address_classifier_bearer');
        if (! $bearer) {
            throw new \RuntimeException('UP_ADDRESS_CLASSIFIER_BEARER is not configured — see config/ukrposhta.php.');
        }

        $url = rtrim((string) config('ukrposhta.address_classifier_base_url'), '/').$endpoint;

        $response = Http::timeout((int) config('ukrposhta.timeout', 10))
            ->withToken($bearer)
            ->acceptJson()
            ->retry(2, 500)
            ->get($url, $query);

        if ($response->failed()) {
            throw new \RuntimeException("Ukrposhta address-classifier request failed: HTTP {$response->status()}");
        }

        return $response->json('Entries.Entry') ?? [];
    }
}
