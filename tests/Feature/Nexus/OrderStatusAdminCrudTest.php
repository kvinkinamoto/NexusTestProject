<?php

use App\Models\User;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/OrderStatus/action/create')->assertOk();
});

it('creates an order status through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'OrderStatus'])
        ->set('data.name.'.config('app.locale'), 'New')
        ->set('data.slug', 'new')
        ->set('data.is_default', true)
        ->set('data.publish', true)
        ->call('save')
        ->assertHasNoErrors();

    $status = OrderStatus::where('slug', 'new')->firstOrFail();

    expect($status->is_default)->toBeTrue();
    expect($status->publish)->toBeTrue();
});

it('toggles publish through the boolToggle table action', function () {
    $status = OrderStatus::create(['name' => 'Cancelled', 'slug' => 'cancelled', 'publish' => true]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'OrderStatus'])
        ->call('toggleBool', (string) $status->id, 'publish');

    expect($status->refresh()->publish)->toBeFalse();
});

it('filters the default status via scopeDefault', function () {
    OrderStatus::create(['name' => 'New', 'slug' => 'new', 'is_default' => true]);
    OrderStatus::create(['name' => 'Confirmed', 'slug' => 'confirmed', 'is_default' => false]);

    expect(OrderStatus::query()->default()->first()?->slug)->toBe('new');
});
