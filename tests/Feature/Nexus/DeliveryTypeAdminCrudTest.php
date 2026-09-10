<?php

use App\Models\User;
use App\Nexus\Modules\Delivery\Models\Delivery;
use App\Nexus\Modules\DeliveryType\Models\DeliveryType;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Delivery'])->run();
    $this->artisan('nexus:module:install', ['name' => 'DeliveryType'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);

    $this->delivery = Delivery::create(['title' => 'Nova Poshta', 'key' => 'nova-poshta', 'publish' => true]);
});

it('mounts the create form without error', function () {
    $this->get('/admin/DeliveryType/action/create')->assertOk();
});

it('creates a delivery type linked to a delivery', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'DeliveryType'])
        ->set('data.title.'.config('app.locale'), 'Warehouse pickup')
        ->set('data.key', 'department')
        ->set('data.delivery', (string) $this->delivery->id)
        ->set('data.publish', true)
        ->call('save')
        ->assertHasNoErrors();

    $type = DeliveryType::where('key', 'department')->firstOrFail();

    expect($type->delivery_id)->toBe($this->delivery->id);
    expect($type->delivery->key)->toBe('nova-poshta');
});

it('allows a delivery type with no parent delivery', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'DeliveryType'])
        ->set('data.title.'.config('app.locale'), 'Standalone')
        ->set('data.key', 'standalone')
        ->call('save')
        ->assertHasNoErrors();

    expect(DeliveryType::where('key', 'standalone')->firstOrFail()->delivery_id)->toBeNull();
});
