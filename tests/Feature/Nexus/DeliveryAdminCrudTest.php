<?php

use App\Models\User;
use App\Nexus\Modules\Delivery\Models\Delivery;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Delivery'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Delivery/action/create')->assertOk();
});

it('creates a delivery through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Delivery'])
        ->set('data.title.'.config('app.locale'), 'Nova Poshta')
        ->set('data.key', 'nova-poshta')
        ->set('data.publish', true)
        ->call('save')
        ->assertHasNoErrors();

    $delivery = Delivery::where('key', 'nova-poshta')->firstOrFail();

    expect($delivery->publish)->toBeTrue();
});

it('rejects a duplicate key', function () {
    Delivery::create(['title' => 'Nova Poshta', 'key' => 'nova-poshta']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Delivery'])
        ->set('data.title.'.config('app.locale'), 'Duplicate')
        ->set('data.key', 'nova-poshta')
        ->call('save')
        ->assertHasErrors(['data.key']);
});

it('toggles publish through the boolToggle table action', function () {
    $delivery = Delivery::create(['title' => 'Pickup', 'key' => 'pickup', 'publish' => true]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'Delivery'])
        ->call('toggleBool', (string) $delivery->id, 'publish');

    expect($delivery->refresh()->publish)->toBeFalse();
});
