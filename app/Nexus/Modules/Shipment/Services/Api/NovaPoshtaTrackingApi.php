<?php

namespace App\Nexus\Modules\Shipment\Services\Api;

use Illuminate\Support\Facades\Http;

/**
 * A minimal client for Nova Poshta's InternetDocument tracking method only —
 * ZentaraStartProject's `sheva/novaposhta-delivery-module` package also
 * covers address-classifier sync (cities/warehouses/streets, for a
 * checkout autocomplete UI) and shipment creation, neither of which has a
 * consumer here (no storefront theme exists in Nexus yet), so only the
 * tracking envelope is ported.
 */
class NovaPoshtaTrackingApi
{
    public function track(string $documentNumber): array
    {
        $url = rtrim((string) config('novaposhta.base_uri'), '/').'/'.config('novaposhta.point').'/';

        $response = Http::timeout(20)->retry(2, 200)->acceptJson()->post($url, [
            'apiKey' => config('novaposhta.api_key'),
            'modelName' => 'TrackingDocument',
            'calledMethod' => 'getStatusDocuments',
            'methodProperties' => [
                'Documents' => [['DocumentNumber' => $documentNumber]],
            ],
        ]);

        if ($response->failed()) {
            return ['success' => false, 'result' => null, 'info' => []];
        }

        $data = $response->json();
        $answer = $data[0] ?? $data;

        if (empty($answer['success'])) {
            return ['success' => false, 'result' => null, 'info' => ['errorsLocale' => $answer['errors'] ?? []]];
        }

        return ['success' => true, 'result' => $answer['data'], 'info' => []];
    }
}
