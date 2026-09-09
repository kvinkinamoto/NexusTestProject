<?php

use App\Models\User;
use App\Nexus\Modules\ShopProductType\Models\ShopProductType;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProductType'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/ShopProductType/action/create')->assertOk();
});

it('creates a product type through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopProductType'])
        ->set('data.name.'.config('app.locale'), 'Variable')
        ->call('save')
        ->assertHasNoErrors();

    expect(ShopProductType::query()->count())->toBe(1);
    expect(ShopProductType::query()->first()->name)->toBe('Variable');
});
