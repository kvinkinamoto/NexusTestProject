<?php

use App\Models\User;
use App\Nexus\Modules\ShopAttribute\Enums\AttributeType;
use App\Nexus\Modules\ShopAttribute\Models\ShopAttribute;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopAttribute'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/ShopAttribute/action/create')->assertOk();
});

it('creates an attribute with its options through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'ShopAttribute'])
        ->set('data.name.'.config('app.locale'), 'Color')
        ->set('data.type', AttributeType::COLOR->value)
        ->set('data.publish', true)
        ->set('relationRows.options', [
            ['value' => 'Red', 'color' => '#ff0000'],
            ['value' => 'Blue', 'color' => '#0000ff'],
        ])
        ->call('save')
        ->assertHasNoErrors();

    $attribute = ShopAttribute::query()->firstOrFail();
    $options = $attribute->options()->orderBy('order')->get();

    expect($attribute->type)->toBe(AttributeType::COLOR->value);
    expect($options)->toHaveCount(2);
    expect($options[0]->value)->toBe('Red');
    expect($options[1]->color)->toBe('#0000ff');
});

it('toggles publish through the boolToggle table action', function () {
    $attribute = ShopAttribute::create(['name' => 'Size', 'type' => AttributeType::TEXT->value, 'publish' => true]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'ShopAttribute'])
        ->call('toggleBool', (string) $attribute->id, 'publish');

    expect($attribute->refresh()->publish)->toBeFalse();
});
