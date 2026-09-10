<?php

use App\Models\User;
use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use App\Nexus\Modules\Bonus\Models\BonusRule;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Bonus'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Bonus/action/create')->assertOk();
});

it('creates a bonus rule through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Bonus'])
        ->set('data.type', BonusTypeEnum::PURCHASE->value)
        ->set('data.amount', 0)
        ->set('data.delay_days', 15)
        ->set('data.validity_days', 180)
        ->set('data.is_active', true)
        ->call('save')
        ->assertHasNoErrors();

    $rule = BonusRule::where('type', BonusTypeEnum::PURCHASE)->firstOrFail();

    expect($rule->delay_days)->toBe(15);
    expect($rule->is_active)->toBeTrue();
});

it('rejects a duplicate type', function () {
    BonusRule::create(['type' => BonusTypeEnum::REGISTRATION, 'amount' => 100]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Bonus'])
        ->set('data.type', BonusTypeEnum::REGISTRATION->value)
        ->set('data.amount', 50)
        ->call('save')
        ->assertHasErrors(['data.type']);
});

it('toggles is_active through the boolToggle table action', function () {
    $rule = BonusRule::create(['type' => BonusTypeEnum::MANUAL, 'amount' => 0, 'is_active' => true]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'Bonus'])
        ->call('toggleBool', (string) $rule->id, 'is_active');

    expect($rule->refresh()->is_active)->toBeFalse();
});
