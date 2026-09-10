<?php

use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;
use App\Nexus\Modules\Shipment\Models\OrderShipment;
use App\Nexus\Modules\Shipment\Services\Actions\RefreshShipmentStatusAction;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    $this->action = app(RefreshShipmentStatusAction::class);
});

function makeShipment(string $carrier = 'nova-poshta', DeliveryStatus $status = DeliveryStatus::Registered): OrderShipment
{
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'delivery_method' => $carrier]);

    return OrderShipment::create([
        'order_id' => $order->id,
        'carrier' => $carrier,
        'tracking_number' => '20450000000000',
        'delivery_status' => $status,
    ]);
}

it('throws for a shipment with no carrier or tracking number', function () {
    $shipment = OrderShipment::create(['order_id' => Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000'])->id]);

    $this->action->handle($shipment);
})->throws(RuntimeException::class);

it('updates status and records an event when the status changed', function () {
    Http::fake([
        'api.novaposhta.ua/*' => Http::response(['success' => true, 'data' => [['Status' => 'У відділенні', 'StatusCode' => '7']]], 200),
    ]);

    $shipment = makeShipment();

    $updated = $this->action->handle($shipment);

    expect($updated->delivery_status)->toBe(DeliveryStatus::AtWarehouse);
    expect($updated->last_checked_at)->not->toBeNull();
    expect($updated->shipped_at)->not->toBeNull();
    expect($updated->events()->count())->toBe(1);
});

it('does not duplicate an event when the status is unchanged', function () {
    Http::fake([
        'api.novaposhta.ua/*' => Http::response(['success' => true, 'data' => [['Status' => 'Зареєстровано', 'StatusCode' => '1']]], 200),
    ]);

    $shipment = makeShipment(status: DeliveryStatus::Registered);

    $this->action->handle($shipment);

    expect($shipment->events()->count())->toBe(0);
});

it('sets delivered_at once the status becomes Delivered', function () {
    Http::fake([
        'api.novaposhta.ua/*' => Http::response(['success' => true, 'data' => [['Status' => 'Вручено', 'StatusCode' => '9']]], 200),
    ]);

    $shipment = makeShipment(status: DeliveryStatus::InTransit);

    $updated = $this->action->handle($shipment);

    expect($updated->delivery_status)->toBe(DeliveryStatus::Delivered);
    expect($updated->delivered_at)->not->toBeNull();
});

it('still records last_checked_at and throws when the carrier call fails', function () {
    Http::fake(['api.novaposhta.ua/*' => Http::response([], 500)]);

    $shipment = makeShipment();

    try {
        $this->action->handle($shipment);
    } catch (RuntimeException $e) {
        // expected
    }

    expect($shipment->fresh()->last_checked_at)->not->toBeNull();
    expect($shipment->fresh()->delivery_status)->toBe(DeliveryStatus::Registered);
});
