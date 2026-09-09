<?php

use App\Models\User;
use App\Nexus\Modules\Currency\Enums\CurrencySymbolPosition;
use App\Nexus\Modules\Currency\Models\Currency;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Currency'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Currency/action/create')->assertOk();
});

it('creates a currency through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Currency'])
        ->set('data.code', 'USD')
        ->set('data.name.'.config('app.locale'), 'US Dollar')
        ->set('data.symbol', '$')
        ->set('data.symbol_position', CurrencySymbolPosition::BEFORE->value)
        ->set('data.rate', 41.5)
        ->set('data.is_base', false)
        ->set('data.publish', true)
        ->call('save')
        ->assertHasNoErrors();

    $currency = Currency::where('code', 'USD')->firstOrFail();

    expect($currency->symbol)->toBe('$');
    expect($currency->symbol_position)->toBe(CurrencySymbolPosition::BEFORE->value);
    expect((float) $currency->rate)->toBe(41.5);
    expect($currency->publish)->toBeTrue();
});

it('rejects a duplicate currency code', function () {
    Currency::create(['code' => 'UAH', 'symbol_position' => CurrencySymbolPosition::AFTER->value, 'rate' => 1, 'is_base' => true]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Currency'])
        ->set('data.code', 'UAH')
        ->set('data.symbol_position', CurrencySymbolPosition::AFTER->value)
        ->set('data.rate', 1)
        ->call('save')
        ->assertHasErrors(['data.code' => 'unique']);
});

it('toggles publish through the boolToggle table action', function () {
    $currency = Currency::create([
        'code' => 'EUR',
        'symbol_position' => CurrencySymbolPosition::BEFORE->value,
        'rate' => 45.0,
        'is_base' => false,
        'publish' => true,
    ]);

    Livewire::test(ModuleTable::class, ['moduleName' => 'Currency'])
        ->call('toggleBool', (string) $currency->id, 'publish');

    expect($currency->refresh()->publish)->toBeFalse();
});
