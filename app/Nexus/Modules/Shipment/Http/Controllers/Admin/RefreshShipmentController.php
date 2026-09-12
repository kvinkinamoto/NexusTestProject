<?php

namespace App\Nexus\Modules\Shipment\Http\Controllers\Admin;

use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\Shipment\Http\Resources\OrderShipmentStatusResource;
use App\Nexus\Modules\Shipment\Models\OrderShipment;
use App\Nexus\Modules\Shipment\Services\Actions\RefreshShipmentStatusAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use RuntimeException;

class RefreshShipmentController extends Controller
{
    public function __construct(
        private readonly RefreshShipmentStatusAction $refreshShipmentStatusAction,
    ) {}

    public function __invoke(Order $order): JsonResponse
    {
        $shipment = OrderShipment::query()->where('order_id', $order->id)->first();

        if (! $shipment || empty($shipment->tracking_number)) {
            return response()->json(['message' => 'This order has no tracking number set.'], 422);
        }

        try {
            $shipment = $this->refreshShipmentStatusAction->handle($shipment);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json(new OrderShipmentStatusResource($shipment));
    }
}
