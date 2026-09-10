<?php

namespace App\Nexus\Modules\Shipment\Services;

use App\Nexus\Modules\Shipment\Services\Trackers\NovaPoshtaTracker;
use App\Nexus\Modules\Shipment\Services\Trackers\TrackerInterface;
use App\Nexus\Modules\Shipment\Services\Trackers\UkrPoshtaTracker;
use InvalidArgumentException;

class TrackingManager
{
    /**
     * Carrier keys that support tracking — a subset of Delivery.key (the
     * same keys the customer picks as Order.delivery_method at checkout).
     * There is no separate "tracking carrier" selection — the carrier is
     * always the order's own delivery_method.
     */
    public const TRACKABLE_CARRIERS = ['nova-poshta', 'ukr-poshta'];

    public static function isTrackable(?string $carrier): bool
    {
        return in_array($carrier, self::TRACKABLE_CARRIERS, true);
    }

    public function driver(string $carrier): TrackerInterface
    {
        return match ($carrier) {
            'nova-poshta' => app(NovaPoshtaTracker::class),
            'ukr-poshta' => app(UkrPoshtaTracker::class),
            default => throw new InvalidArgumentException("Unknown tracking carrier: {$carrier}"),
        };
    }
}
