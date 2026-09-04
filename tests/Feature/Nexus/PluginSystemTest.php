<?php

use App\Nexus\Plugins\Example\ExamplePlugin;
use Nodex\Nexus\Services\FieldTypeRegistry;
use Nodex\Nexus\Services\HookManager;
use Nodex\Nexus\Services\ModuleManager;
use Nodex\Nexus\Services\PluginManager;
use Nodex\Nexus\Services\Validation\NexusRuleCollector;

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
