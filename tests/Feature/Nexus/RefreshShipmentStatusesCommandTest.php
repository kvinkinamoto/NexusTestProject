<?php

use App\Nexus\Modules\Order\Commands\RefreshShipmentStatuses;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;
use App\Nexus\Modules\Shipment\Models\OrderShipment;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();
});

/**
 * Module-provided artisan commands aren't registered in time to be called
 * via $this->artisan('shipment:refresh-statuses') in Pest (registration
 * happens once at boot, before nexus:module:install runs in beforeEach) —
 * per project convention, run the command's own Symfony lifecycle directly
 * instead, which initializes $this->output before handle() touches it.
 */
function runRefreshShipmentStatuses(): void
{
    $command = app(RefreshShipmentStatuses::class);
    $command->setLaravel(app());
    $command->run(new ArrayInput([]), new NullOutput);
}

it('refreshes only active (non-terminal) shipments', function () {
    Http::fake([
        'api.novaposhta.ua/*' => Http::response(['success' => true, 'data' => [['Status' => 'У відділенні', 'StatusCode' => '7']]], 200),
    ]);

    $active = OrderShipment::create([
        'order_id' => Order::create(['user_name' => 'A', 'user_phone' => '1'])->id,
        'carrier' => 'nova-poshta',
        'tracking_number' => '111',
        'delivery_status' => DeliveryStatus::Registered,
    ]);
    $delivered = OrderShipment::create([
        'order_id' => Order::create(['user_name' => 'B', 'user_phone' => '2'])->id,
        'carrier' => 'nova-poshta',
        'tracking_number' => '222',
        'delivery_status' => DeliveryStatus::Delivered,
    ]);

    runRefreshShipmentStatuses();

    expect($active->fresh()->delivery_status)->toBe(DeliveryStatus::AtWarehouse);
    expect($delivered->fresh()->delivery_status)->toBe(DeliveryStatus::Delivered);
    Http::assertSentCount(1);
});

it('skips shipments with no tracking number', function () {
    Http::fake();

    OrderShipment::create([
        'order_id' => Order::create(['user_name' => 'A', 'user_phone' => '1'])->id,
        'carrier' => 'nova-poshta',
        'tracking_number' => null,
        'delivery_status' => DeliveryStatus::Registered,
    ]);

    runRefreshShipmentStatuses();

    Http::assertNothingSent();
});
