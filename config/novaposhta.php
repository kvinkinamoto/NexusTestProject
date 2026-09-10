<?php

/**
 * Trimmed to the keys App\Nexus\Modules\Shipment\Services\Api\NovaPoshtaTrackingApi
 * actually reads (TTN status lookup only) — ZentaraStartProject's own config
 * also carried shipment-creation/address-classifier settings
 * (recipient_type/service_type/cargo_type/payment_method/payer_type/
 * back_delivery_*) that have no consumer in this port; see
 * NovaPoshtaTrackingApi's docblock for why.
 */
return [
    /**
     * @see https://devcenter.novaposhta.ua/
     */
    'base_uri' => 'https://api.novaposhta.ua/v2.0/',

    /**
     * novaposhta supported: "json", "xml" — this client only supports "json"
     */
    'point' => 'json',

    'api_key' => env('NP_API_KEY', ''),
];
