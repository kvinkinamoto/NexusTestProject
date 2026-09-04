<?php

use App\Models\User;
use App\Nexus\Plugins\Example\ExamplePlugin;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Services\FieldTypeRegistry;
use Nodex\Nexus\Services\HookManager;
use Nodex\Nexus\Services\ModuleManager;
use Nodex\Nexus\Services\PluginManager;
use Nodex\Nexus\Services\Validation\NexusRuleCollector;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for the plugin ecosystem fix: PluginManager::autoDiscover()
 * previously found nothing (no app/Nexus/Plugins directory existed) and
 * nexus:make:plugin generated empty, non-functional files (missing stubs,
 * same failure mode nexus:make:module had before it was fixed). These tests
 * exercise the real, working App\Nexus\Plugins\Example\ExamplePlugin
 * end-to-end: discover → registerAll/bootAll → the 3 hook points actually
 * called anywhere in the package, plus #[TargetModule] config mutation.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();
});

it('mutates the target module\'s config via #[TargetModule] + handle()', function () {
    $config = ModuleManager::getModuleConfig('Demo');

    expect($config->menu->label)->toEndWith(' ⚡');
});

it('extends validation rules through the nexus.validation.rules filter', function () {
    $config = ModuleManager::getModuleConfig('Demo');
    $rules = app(NexusRuleCollector::class)->collect($config, 'store');

    expect($rules['title'])->toContain('max:100');
});

it('registers a field type through the nexus.field_types.register action', function () {
    $registry = app(FieldTypeRegistry::class);

    expect($registry->resolveType('examplePluginAlias'))->toBe('string');
});

it('skips a plugin listed in config(nexus.plugins.disabled)', function () {
    config(['nexus.plugins.disabled' => [ExamplePlugin::class]]);

    $manager = new PluginManager(app(HookManager::class));
    $manager->autoDiscover();

    expect($manager->getPlugins('Demo'))->toBe([]);
});

it('still discovers the plugin when it is not disabled', function () {
    $manager = new PluginManager(app(HookManager::class));
    $manager->autoDiscover();

    expect($manager->getPlugins('Demo'))->toContain(ExamplePlugin::class);
});

it('applies the nexus.validation.rules filter to a real Demo store request through its dedicated AdminStoreRequest', function () {
    // Regression test for the gap this filter used to have: Demo's own
    // AdminStoreRequest (app/Nexus/Modules/Demo/Requests/AdminStoreRequest.php)
    // hand-writes rules() directly, which previously meant NexusRuleCollector
    // ::collect() — and so this filter — never ran for a real Demo save.
    // NexusFormRequest::withValidator() (final, un-overridable) now closes
    // that gap for every dedicated Request regardless of how it built its
    // rules(). Goes through the real HTTP route (not Livewire::test() or a
    // direct collect() call) specifically so request()->route('module')/
    // ('action') — what withValidator() resolves the module/action from —
    // are populated exactly as they would be for a real browser submission.
    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');
    $admin = User::factory()->create();
    $admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);

    $response = $this->actingAs($admin)->post(
        route('nexus.module.action', ['module' => 'demo', 'action' => 'store']),
        ['title' => str_repeat('a', 101)],
    );

    $response->assertSessionHasErrors('title');
});
