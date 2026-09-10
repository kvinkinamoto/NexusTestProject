<?php

/**
 * Trimmed to the Status Tracking Service keys
 * App\Nexus\Modules\Shipment\Services\Api\UkrPoshtaTrackingApi actually
 * reads — ZentaraStartProject's own config also carried the main eCom API
 * settings (base_url/api_token, for shipment creation and the
 * address-classifier sync) that have no consumer in this port.
 */
return [
    'timeout' => env('UKRPOSHTA_TIMEOUT', 10),

    /**
     * @see https://www.ukrposhta.ua/status-tracking/2.0
     */
    'status_tracking_base_url' => env('UP_STATUS_TRACKING_BASE_URL', 'https://www.ukrposhta.ua/status-tracking/2.0'),

    'status_tracking_bearer' => env('UP_STATUS_TRACKING_BEARER', ''),
];
