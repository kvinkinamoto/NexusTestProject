<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use Illuminate\Http\UploadedFile;
use Livewire\Livewire;
use Nodex\Nexus\Contracts\MediaLibrary\MediaLibraryInterface;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleTable;
use Nodex\Nexus\Notifications\ModuleInstalledNotification;
use Nodex\Nexus\Services\HookManager;
use Nodex\Nexus\Services\ModuleRegistry;
use Nodex\Nexus\Services\Widgets\DashboardLayoutResolver;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for the 5 genuinely-missing extension points closed in
 * this pass — areas that had NO hook or event at all before, not just a
 * hook missing its event pair: dashboard-layout defaults, a "before" point
 * for bulk actions, media uploads, notification content, and module
 * discovery.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('lets a plugin override the resolved dashboard layout via nexus.dashboard.layout', function () {
    app(HookManager::class)->addFilter('nexus.dashboard.layout', function (array $layout) {
        return ['pluginWidget', ...$layout];
    }, 10);

    $layout = app(DashboardLayoutResolver::class)->resolveFor($this->admin);

    expect($layout)->toContain('pluginWidget');
});

it('lets a plugin drop an id from a bulk action before it runs via nexus.bulk_action.executing', function () {
    $keep = Demo::create(['title' => 'Keep me']);
    $blocked = Demo::create(['title' => 'Block me']);

    app(HookManager::class)->addFilter('nexus.bulk_action.executing', function (array $ids) use ($blocked) {
        return array_values(array_diff($ids, [(string) $blocked->id]));
    }, 10);

    Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->set('selected', [(string) $keep->id, (string) $blocked->id])
        ->call('runGroupAction', 'deleteGroup');

    expect(Demo::find($keep->id))->toBeNull()
        ->and(Demo::find($blocked->id))->not->toBeNull();
});

it('lets a plugin reject an upload via nexus.media.attaching and react via nexus.media.attached', function () {
    $demo = Demo::create(['title' => 'Media hook check']);

    $attachingFired = false;
    $attachedFired = false;

    app(HookManager::class)->addAction('nexus.media.attaching', function () use (&$attachingFired) {
        $attachingFired = true;
    }, 10);
    app(HookManager::class)->addAction('nexus.media.attached', function ($model, $item) use (&$attachedFired) {
        $attachedFired = true;
    }, 10);

    $file = UploadedFile::fake()->image('demo-photo.jpg');
    app(MediaLibraryInterface::class)->attach($demo, $file, 'default');

    expect($attachingFired)->toBeTrue()
        ->and($attachedFired)->toBeTrue();
});

it('lets a plugin enrich the module-installed notification payload via nexus.notification.data', function () {
    app(HookManager::class)->addFilter('nexus.notification.data', function (array $data) {
        $data['pluginMarked'] = true;

        return $data;
    }, 10);

    $data = (new ModuleInstalledNotification('demo'))->toArray($this->admin);

    expect($data)->toHaveKey('pluginMarked', true);
});

it('lets a plugin register a module that has no real directory via nexus.module.discovery', function () {
    app(HookManager::class)->addFilter('nexus.module.discovery', function ($modules) {
        $modules->put('pluginonlymodule', ['name' => 'PluginOnlyModule', 'path' => __DIR__, 'namespace' => 'Test\\PluginOnly', 'is_user_module' => false]);

        return $modules;
    }, 10);

    // getAllModules() memoizes on the ModuleRegistry singleton — beforeEach()'s
    // nexus:module:install already populated that cache before this test's
    // filter was registered, so force a fresh scan to actually exercise it.
    $registry = app(ModuleRegistry::class);
    $registry->refresh();
    $modules = $registry->getAllModules();

    expect($modules->has('pluginonlymodule'))->toBeTrue();
});
