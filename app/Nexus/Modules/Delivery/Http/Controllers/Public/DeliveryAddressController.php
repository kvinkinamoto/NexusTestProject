<?php

namespace App\Nexus\Modules\Delivery\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Delivery\Http\Resources\DeliveryCityResource;
use App\Nexus\Modules\Delivery\Http\Resources\DeliveryWarehouseResource;
use App\Nexus\Modules\Delivery\Models\Delivery;
use App\Nexus\Modules\Delivery\Models\DeliveryWarehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Backs the checkout's city/warehouse picker for carriers with a synced
 * address classifier (see DeliveryAddressSyncService) — a local DB search
 * against the cached delivery_cities/delivery_warehouses tables, not a live
 * carrier API call per keystroke.
 */
class DeliveryAddressController extends Controller
{
    public function cities(Request $request, string $deliveryKey): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        if (mb_strlen($q) < 2) {
            return response()->json(['cities' => []]);
        }

        $delivery = Delivery::where('key', $deliveryKey)->first();
        if (! $delivery) {
            return response()->json(['cities' => []]);
        }

        $cities = $delivery->cities()
            ->where('name', 'like', $q.'%')
            ->orderByRaw('LENGTH(name) asc')
            ->take(10)
            ->get(['id', 'name', 'region']);

        return response()->json(['cities' => DeliveryCityResource::collection($cities)]);
    }

    public function warehouses(Request $request, int $cityId): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));

        $warehouses = DeliveryWarehouse::where('delivery_city_id', $cityId)
            ->when($q !== '', fn ($query) => $query->where(function ($query) use ($q) {
                $query->where('number', 'like', $q.'%')->orWhere('name', 'like', '%'.$q.'%');
            }))
            ->orderByRaw('CAST(number AS UNSIGNED) asc')
            ->take(50)
            ->get(['id', 'number', 'name', 'address', 'type']);

        return response()->json(['warehouses' => DeliveryWarehouseResource::collection($warehouses)]);
    }
}
