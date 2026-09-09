<?php

use App\Models\User;
use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\Promotion\Models\Promotion;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'DiscountRule'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Promotion'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('mounts the create form without error', function () {
    $this->get('/admin/Promotion/action/create')->assertOk();
});

it('creates a promotion pointing at a discount rule', function () {
    $rule = DiscountRule::create(['name' => 'Sale', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Promotion'])
        ->set('data.name.'.config('app.locale'), 'Summer Sale')
        ->set('data.publish', true)
        ->set('data.discountRule', (string) $rule->id)
        ->call('save')
        ->assertHasNoErrors();

    $promotion = Promotion::query()->firstOrFail();
    expect($promotion->name)->toBe('Summer Sale');
    expect($promotion->discount_rule_id)->toBe($rule->id);
});

it('reports draft status when not published', function () {
    $rule = DiscountRule::create(['name' => 'Sale', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value]);
    $promotion = Promotion::create(['name' => 'Sale', 'discount_rule_id' => $rule->id, 'publish' => false]);

    expect($promotion->status)->toBe('draft');
});

it('reports scheduled status when starts_at is in the future', function () {
    $rule = DiscountRule::create(['name' => 'Sale', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value]);
    $promotion = Promotion::create(['name' => 'Sale', 'discount_rule_id' => $rule->id, 'publish' => true, 'starts_at' => now()->addDay()]);

    expect($promotion->status)->toBe('scheduled');
    expect($promotion->isCurrentlyActive())->toBeFalse();
});

it('reports active status when published and within the window', function () {
    $rule = DiscountRule::create(['name' => 'Sale', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value]);
    $promotion = Promotion::create(['name' => 'Sale', 'discount_rule_id' => $rule->id, 'publish' => true]);

    expect($promotion->status)->toBe('active');
    expect($promotion->isCurrentlyActive())->toBeTrue();
});

it('reports expired status once expires_at has passed', function () {
    $rule = DiscountRule::create(['name' => 'Sale', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value]);
    $promotion = Promotion::create(['name' => 'Sale', 'discount_rule_id' => $rule->id, 'publish' => true, 'expires_at' => now()->subDay()]);

    expect($promotion->status)->toBe('expired');
});
