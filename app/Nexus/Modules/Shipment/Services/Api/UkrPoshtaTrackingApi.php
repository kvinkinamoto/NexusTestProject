<?php

namespace App\Nexus\Modules\Shipment\Services\Api;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * A minimal client for Ukrposhta's separately-authenticated Status Tracking
 * Service only — the full `sheva/ukrposhta-delivery-module` package also
 * covers address-classifier sync (regions/districts/cities/post offices,
 * for a checkout autocomplete UI), which has no consumer here.
 */
class UkrPoshtaTrackingApi
{
    public function track(string $barcode): array
    {
        try {
            $response = Http::timeout((int) config('ukrposhta.timeout', 10))
                ->retry(2, 1000)
                ->acceptJson()
                ->withToken((string) config('ukrposhta.status_tracking_bearer'))
                ->get(rtrim((string) config('ukrposhta.status_tracking_base_url'), '/').'/statuses/last', ['barcode' => $barcode]);

            if ($response->failed()) {
                Log::warning('Ukrposhta status-tracking request failed', ['barcode' => $barcode, 'response' => $response->json()]);

                return ['success' => false, 'result' => null];
            }

            return ['success' => true, 'result' => $response->json()];
        } catch (\Exception $e) {
            Log::warning('Ukrposhta status-tracking request errored', ['barcode' => $barcode, 'error' => $e->getMessage()]);

            return ['success' => false, 'result' => null];
        }
    }
}
