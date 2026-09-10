<?php

namespace App\Nexus\Modules\Shipment\Services\Trackers;

use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;

final class TrackingResult
{
    public function __construct(
        public readonly bool $success,
        public readonly ?string $rawStatus,
        public readonly ?string $rawStatusCode,
        public readonly DeliveryStatus $normalizedStatus,
        public readonly ?string $errorMessage = null,
    ) {}
}
