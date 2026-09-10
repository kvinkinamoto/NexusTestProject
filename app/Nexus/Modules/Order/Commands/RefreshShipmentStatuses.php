<?php

namespace App\Nexus\Modules\Order\Commands;

use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;
use App\Nexus\Modules\Shipment\Models\OrderShipment;
use App\Nexus\Modules\Shipment\Services\Actions\RefreshShipmentStatusAction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class RefreshShipmentStatuses extends Command
{
    protected $signature = 'shipment:refresh-statuses';

    protected $description = 'Poll Nova Poshta / Ukrposhta for the latest status of every active (non-terminal) shipment.';

    private const TERMINAL_STATUSES = [
        DeliveryStatus::Delivered,
        DeliveryStatus::Returned,
        DeliveryStatus::Cancelled,
    ];

    public function handle(RefreshShipmentStatusAction $action): int
    {
        $shipments = OrderShipment::query()
            ->whereNotNull('tracking_number')
            ->whereNotIn('delivery_status', array_map(fn (DeliveryStatus $s) => $s->value, self::TERMINAL_STATUSES))
            ->get();

        $this->info("Refreshing {$shipments->count()} active shipment(s)...");

        $succeeded = 0;
        $failed = 0;

        foreach ($shipments as $shipment) {
            try {
                $action->handle($shipment);
                $succeeded++;
            } catch (Throwable $e) {
                $failed++;
                Log::warning("shipment:refresh-statuses failed for order_shipment #{$shipment->id}: {$e->getMessage()}");
            }
        }

        $this->info("Done: {$succeeded} succeeded, {$failed} failed.");

        return self::SUCCESS;
    }
}
