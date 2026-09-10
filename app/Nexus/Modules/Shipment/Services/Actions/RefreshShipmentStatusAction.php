<?php

namespace App\Nexus\Modules\Shipment\Services\Actions;

use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;
use App\Nexus\Modules\Shipment\Models\OrderShipment;
use App\Nexus\Modules\Shipment\Services\TrackingManager;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RefreshShipmentStatusAction
{
    public function __construct(
        private readonly TrackingManager $trackingManager,
    ) {}

    /**
     * @throws RuntimeException when the carrier API call itself fails (e.g. missing credentials)
     */
    public function handle(OrderShipment $shipment): OrderShipment
    {
        if (empty($shipment->carrier) || empty($shipment->tracking_number)) {
            throw new RuntimeException('Shipment has no carrier/tracking number set.');
        }

        $tracker = $this->trackingManager->driver($shipment->carrier);
        $result = $tracker->track($shipment->tracking_number);

        DB::transaction(function () use ($shipment, $result) {
            $shipment->last_checked_at = now();

            if ($result->success) {
                $statusChanged = $shipment->delivery_status !== $result->normalizedStatus;

                $shipment->raw_status = $result->rawStatus;
                $shipment->raw_status_code = $result->rawStatusCode;
                $shipment->delivery_status = $result->normalizedStatus;

                if (! $shipment->shipped_at && ! in_array($result->normalizedStatus, [DeliveryStatus::Unknown, DeliveryStatus::Registered], true)) {
                    $shipment->shipped_at = now();
                }

                if (! $shipment->delivered_at && $result->normalizedStatus === DeliveryStatus::Delivered) {
                    $shipment->delivered_at = now();
                }

                if ($statusChanged) {
                    $shipment->events()->create([
                        'delivery_status' => $result->normalizedStatus,
                        'raw_status' => $result->rawStatus,
                        'raw_status_code' => $result->rawStatusCode,
                    ]);
                }
            }

            $shipment->save();
        });

        if (! $result->success) {
            throw new RuntimeException($result->errorMessage ?? 'Tracking request failed.');
        }

        return $shipment->refresh();
    }
}
