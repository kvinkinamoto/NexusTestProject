<?php

use App\Models\User;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\Shipment\Enums\DeliveryStatus;
use App\Nexus\Modules\Shipment\Models\OrderShipment;
use Illuminate\Support\Facades\Http;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('refreshes a shipment and returns its status', function () {
    Http::fake([
        'api.novaposhta.ua/*' => Http::response(['success' => true, 'data' => [['Status' => 'Вручено', 'StatusCode' => '9']]], 200),
    ]);

    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000']);
    OrderShipment::create([
        'order_id' => $order->id,
        'carrier' => 'nova-poshta',
        'tracking_number' => '20450000000000',
        'delivery_status' => DeliveryStatus::InTransit,
    ]);

    $this->postJson("/admin/shipment/{$order->id}/refresh")
        ->assertOk()
        ->assertJson(['delivery_status' => 'delivered']);
});

it('returns 422 when the order has no tracking number', function () {
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000']);

    $this->postJson("/admin/shipment/{$order->id}/refresh")->assertStatus(422);
});
