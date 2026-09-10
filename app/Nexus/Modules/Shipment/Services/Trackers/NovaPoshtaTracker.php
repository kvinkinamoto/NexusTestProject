<?php

namespace App\Nexus\Modules\Shipment\Services\Trackers;

use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;
use App\Nexus\Modules\Shipment\Services\Api\NovaPoshtaTrackingApi;
use Illuminate\Http\Client\RequestException;

class NovaPoshtaTracker implements TrackerInterface
{
    public function track(string $trackingNumber): TrackingResult
    {
        // NovaPoshtaTrackingApi's ->retry() throws a RequestException once
        // retries are exhausted on a connection failure instead of returning
        // a failed response — catch it here so a transient/misconfigured API
        // call degrades to a normal failure result instead of crashing the
        // refresh endpoint.
        try {
            $response = (new NovaPoshtaTrackingApi)->track($trackingNumber);
        } catch (RequestException $e) {
            return new TrackingResult(
                success: false,
                rawStatus: null,
                rawStatusCode: null,
                normalizedStatus: DeliveryStatus::Unknown,
                errorMessage: $e->getMessage(),
            );
        }

        if (! $response['success']) {
            return new TrackingResult(
                success: false,
                rawStatus: null,
                rawStatusCode: null,
                normalizedStatus: DeliveryStatus::Unknown,
                errorMessage: $this->extractError($response),
            );
        }

        $document = $response['result'][0] ?? null;

        if (! $document) {
            return new TrackingResult(
                success: false,
                rawStatus: null,
                rawStatusCode: null,
                normalizedStatus: DeliveryStatus::Unknown,
                errorMessage: 'Document not found',
            );
        }

        $rawStatus = $document['Status'] ?? null;
        $rawStatusCode = isset($document['StatusCode']) ? (string) $document['StatusCode'] : null;

        // Confirmed live against the real API: StatusCode 3 / "Номер не знайдено" means
        // the tracking number itself is unknown to Nova Poshta — surface this as a
        // failure (wrong TTN) rather than a successful-but-unknown delivery status.
        if ($rawStatusCode === '3' || str_contains(mb_strtolower((string) $rawStatus), 'не знайдено')) {
            return new TrackingResult(
                success: false,
                rawStatus: $rawStatus,
                rawStatusCode: $rawStatusCode,
                normalizedStatus: DeliveryStatus::Unknown,
                errorMessage: $rawStatus ?: 'Tracking number not found',
            );
        }

        $normalizedStatus = DeliveryStatus::fromNovaPoshtaCode($rawStatusCode);

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

    private function extractError(array $response): ?string
    {
        $errors = $response['info']['errorsLocale'] ?? [];

        if (! empty($errors['statusLocale'])) {
            return $errors['statusLocale'];
        }

        if (! empty($errors[0]['statusLocale'])) {
            return $errors[0]['statusLocale'];
        }

        return 'Nova Poshta API request failed';
    }
}
