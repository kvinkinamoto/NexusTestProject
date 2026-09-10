<?php

use App\Models\User;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'PromoCode'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);

    $this->status = OrderStatus::create(['name' => 'New', 'slug' => 'new', 'is_default' => true, 'publish' => true]);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Order/action/create')->assertOk();
});

it('lists orders on the bare index route', function () {
    $this->get('/admin/Order/action/index')->assertOk();
});

it('creates an order through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Order'])
        ->set('data.user_name', 'Bob')
        ->set('data.user_phone', '+380000000')
        ->set('data.status', (string) $this->status->id)
        ->call('save')
        ->assertHasNoErrors();

    $order = Order::query()->firstOrFail();

    expect($order->user_name)->toBe('Bob');
    expect($order->status_id)->toBe($this->status->id);
});

it('edits an order\'s status through the generic admin CRUD', function () {
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '+380000000', 'status_id' => $this->status->id]);
    $confirmed = OrderStatus::create(['name' => 'Confirmed', 'slug' => 'confirmed', 'publish' => true]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Order', 'id' => $order->id])
        ->set('data.status', (string) $confirmed->id)
        ->call('save')
        ->assertHasNoErrors();

    expect($order->fresh()->status_id)->toBe($confirmed->id);
});
