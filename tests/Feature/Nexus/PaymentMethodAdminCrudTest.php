<?php

use App\Models\User;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'PaymentMethod'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/PaymentMethod/action/create')->assertOk();
});

it('creates a payment method through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'PaymentMethod'])
        ->set('data.title.'.config('app.locale'), 'Online payment')
        ->set('data.code', 'liqpay')
        ->set('data.provider', 'liqpay')
        ->set('data.type', 'gateway')
        ->set('data.publish', true)
        ->call('save')
        ->assertHasNoErrors();

    $method = PaymentMethod::where('code', 'liqpay')->firstOrFail();

    expect($method->provider)->toBe('liqpay');
    expect($method->type)->toBe('gateway');
    expect($method->publish)->toBeTrue();
});

it('rejects a duplicate code', function () {
    PaymentMethod::create(['code' => 'liqpay', 'provider' => 'liqpay', 'type' => 'gateway', 'title' => 'Online']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'PaymentMethod'])
        ->set('data.title.'.config('app.locale'), 'Duplicate')
        ->set('data.code', 'liqpay')
        ->set('data.provider', 'liqpay')
        ->set('data.type', 'gateway')
        ->call('save')
        ->assertHasErrors(['data.code']);
});

it('toggles publish through the boolToggle table action', function () {
    $method = PaymentMethod::create(['code' => 'cash', 'provider' => 'offline', 'type' => 'offline', 'title' => 'Cash', 'publish' => true]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'PaymentMethod'])
        ->call('toggleBool', (string) $method->id, 'publish');

    expect($method->refresh()->publish)->toBeFalse();
});
