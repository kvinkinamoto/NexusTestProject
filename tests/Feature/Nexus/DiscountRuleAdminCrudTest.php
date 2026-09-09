<?php

use App\Models\User;
use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Enums\ScopeTarget;
use App\Nexus\Modules\DiscountRule\Enums\ValueType;
use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'DiscountRule'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/DiscountRule/action/create')->assertOk();
});

it('creates a discount rule through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'DiscountRule'])
        ->set('data.name', '20% off everything')
        ->set('data.type', DiscountRuleType::PRODUCT_DISCOUNT->value)
        ->set('data.value_type', ValueType::PERCENT->value)
        ->set('data.amount', 20)
        ->set('data.scope_target', ScopeTarget::ALL->value)
        ->set('data.stackable', false)
        ->call('save')
        ->assertHasNoErrors();

    $rule = DiscountRule::where('name', '20% off everything')->firstOrFail();

    expect($rule->type)->toBe(DiscountRuleType::PRODUCT_DISCOUNT);
    expect((float) $rule->amount)->toBe(20.0);
});

it('stores mechanics as a decoded array round-trippable by the engine', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'DiscountRule'])
        ->set('data.name', 'Tiered')
        ->set('data.type', DiscountRuleType::QUANTITY_DISCOUNT->value)
        ->set('data.scope_target', ScopeTarget::ALL->value)
        ->set('data.mechanics', ['tier_rules' => [['qty_from' => 5, 'discount' => 10]]])
        ->call('save')
        ->assertHasNoErrors();

    $rule = DiscountRule::where('name', 'Tiered')->firstOrFail();

    expect($rule->mechanics)->toBe(['tier_rules' => [['qty_from' => 5, 'discount' => 10]]]);
});
