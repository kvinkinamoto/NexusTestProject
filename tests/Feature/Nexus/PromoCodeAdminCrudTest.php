<?php

use App\Models\User;
use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\PromoCode\Models\PromoCode;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'DiscountRule'])->run();
    $this->artisan('nexus:module:install', ['name' => 'PromoCode'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);

    $this->cartLevelRule = DiscountRule::create(['name' => 'Cart 10%', 'type' => DiscountRuleType::CART_THRESHOLD->value]);
    $this->lineLevelRule = DiscountRule::create(['name' => 'Product 10%', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value]);
});

it('mounts the create form without error', function () {
    $this->get('/admin/PromoCode/action/create')->assertOk();
});

it('creates a promo code and normalizes its code to uppercase', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'PromoCode'])
        ->set('data.code', 'summer10')
        ->set('data.discountRule', (string) $this->cartLevelRule->id)
        ->call('save')
        ->assertHasNoErrors();

    $code = PromoCode::query()->firstOrFail();
    expect($code->code)->toBe('SUMMER10');
});

it('rejects a discount rule that is not cart-level', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'PromoCode'])
        ->set('data.code', 'BADRULE')
        ->set('data.discountRule', (string) $this->lineLevelRule->id)
        ->call('save')
        ->assertHasErrors(['data.discountRule']);
});

it('rejects a duplicate code', function () {
    PromoCode::create(['code' => 'SUMMER10', 'discount_rule_id' => $this->cartLevelRule->id]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'PromoCode'])
        ->set('data.code', 'summer10')
        ->set('data.discountRule', (string) $this->cartLevelRule->id)
        ->call('save')
        ->assertHasErrors(['data.code']);
});
