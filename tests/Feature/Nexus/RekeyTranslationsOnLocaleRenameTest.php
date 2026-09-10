<?php

use App\Models\User;
use App\Nexus\Modules\DiscountRule\Enums\DiscountRuleType;
use App\Nexus\Modules\DiscountRule\Models\DiscountRule;
use App\Nexus\Modules\Language\Listeners\RekeyTranslationsOnLocaleRename;
use App\Nexus\Modules\Language\Models\Language;
use App\Nexus\Modules\Promotion\Models\Promotion;
use Illuminate\Support\Facades\Event;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Events\EntityUpdating;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Language'])->run();
    $this->artisan('nexus:module:install', ['name' => 'DiscountRule'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Promotion'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');
    $admin = User::factory()->create();
    $admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($admin);

    // NexusServiceProvider::loadEvents() wires each module's Listeners/
    // folder from the enabled-modules list at provider boot — before this
    // test's own nexus:module:install('Language') call above ran. Same
    // "auto-discovery already happened before the module existed" gotcha
    // as module commands (see feedback_nexus_module_command_discovery_path
    // memory) — register the listener by hand instead of relying on the
    // real boot-time discovery.
    Event::listen(EntityUpdating::class, RekeyTranslationsOnLocaleRename::class);
});

it('rekeys a translated JSON column when a language code is renamed', function () {
    $language = Language::create(['language' => 'Ukrainian (old)', 'code' => 'ua']);
    $rule = DiscountRule::create(['name' => 'Sale', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value]);
    $promotion = Promotion::create([
        'name' => ['en' => 'Summer Sale', 'ua' => 'Літній розпродаж'],
        'discount_rule_id' => $rule->id,
    ]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Language', 'id' => (string) $language->id])
        ->set('data.code', 'uk')
        ->call('save')
        ->assertHasNoErrors();

    $promotion->refresh();
    expect($promotion->getTranslations('name'))->toBe([
        'en' => 'Summer Sale',
        'uk' => 'Літній розпродаж',
    ]);
});

it('does nothing when the code is unchanged', function () {
    $language = Language::create(['language' => 'English', 'code' => 'en']);
    $rule = DiscountRule::create(['name' => 'Sale', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value]);
    $promotion = Promotion::create([
        'name' => ['en' => 'Summer Sale'],
        'discount_rule_id' => $rule->id,
    ]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Language', 'id' => (string) $language->id])
        ->set('data.language', 'English (updated label)')
        ->call('save')
        ->assertHasNoErrors();

    $promotion->refresh();
    expect($promotion->getTranslations('name'))->toBe(['en' => 'Summer Sale']);
});

it('leaves rows with no matching old-locale key untouched', function () {
    $language = Language::create(['language' => 'French (unused)', 'code' => 'fr']);
    $rule = DiscountRule::create(['name' => 'Sale', 'type' => DiscountRuleType::PRODUCT_DISCOUNT->value]);
    $promotion = Promotion::create([
        'name' => ['en' => 'Summer Sale'],
        'discount_rule_id' => $rule->id,
    ]);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Language', 'id' => (string) $language->id])
        ->set('data.code', 'fr2')
        ->call('save')
        ->assertHasNoErrors();

    $promotion->refresh();
    expect($promotion->getTranslations('name'))->toBe(['en' => 'Summer Sale']);
});
