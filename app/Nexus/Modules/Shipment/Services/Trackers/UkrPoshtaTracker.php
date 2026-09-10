<?php

namespace App\Nexus\Modules\Shipment\Services\Trackers;

use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;
use App\Nexus\Modules\Shipment\Services\Api\UkrPoshtaTrackingApi;

class UkrPoshtaTracker implements TrackerInterface
{
    public function track(string $trackingNumber): TrackingResult
    {
        $response = (new UkrPoshtaTrackingApi)->track($trackingNumber);

        if (! $response['success'] || empty($response['result'])) {
            return new TrackingResult(
                success: false,
                rawStatus: null,
                rawStatusCode: null,
                normalizedStatus: DeliveryStatus::Unknown,
                errorMessage: 'Ukrposhta API request failed',
            );
        }

        $result = $response['result'];
        $entry = $result[0] ?? $result;

        $rawStatus = $entry['eventName'] ?? $entry['status'] ?? null;
        $rawStatusCode = isset($entry['event']) ? (string) $entry['event'] : null;

        $normalizedStatus = DeliveryStatus::fromUkrposhtaEventCode($rawStatusCode);

        if ($normalizedStatus === DeliveryStatus::Unknown) {
            $normalizedStatus = DeliveryStatus::guessFromText($rawStatus);
        }

        return new TrackingResult(
            success: true,
            rawStatus: $rawStatus,
            rawStatusCode: $rawStatusCode,
            normalizedStatus: $normalizedStatus,
        );
    }
}
