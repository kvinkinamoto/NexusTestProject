<?php

namespace App\Nexus\Modules\Shipment\Services\Trackers;

interface TrackerInterface
{
    public function track(string $trackingNumber): TrackingResult;
}
