<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use App\Nexus\Modules\WidgetPlacement\Models\WidgetPlacement;
use Illuminate\Http\Request;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Nodex\Nexus\Models\Module;
use Nodex\Nexus\Requests\DefaultGroupActionRequest;
use Nodex\Nexus\Services\Actions\Admin\RestoreActionGroupMethod;
use Nodex\Nexus\Services\FormBuilder;
use Nodex\Nexus\Services\HookManager;
use Nodex\Nexus\Services\ModuleManager;
use Nodex\Nexus\Services\TableBuilder;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for the nexus_filter()/nexus_action() pairs added
 * alongside every pre-existing Laravel event that previously had no hook
 * counterpart at all (EntityCreating/Created, EntityUpdating/Updated,
 * EntityDeleting/Deleted, EntityRestoring/Restored, AdminFormBuilding,
 * FormFieldsPrepared, AdminTableBuilding, TableDataPrepared,
 * ModuleActionExecuted, PreparingForValidation, ModuleInstalled,
 * ModuleUninstalled) — same "deliberately overlapping" design as
 * PluginHookCoverageTest.php/PluginEventCoverageTest.php, just for the
 * older, previously event-only extension points.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();
    $this->artisan('nexus:module:install', ['name' => 'WidgetPlacement'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('fires nexus.entity.creating and nexus.entity.created when a record is created via ModuleForm', function () {
    $creatingFired = false;
    $createdFired = false;

    app(HookManager::class)->addFilter('nexus.entity.creating', function (array $data) use (&$creatingFired) {
        $creatingFired = true;
        $data['title'] = 'HOOKED-'.$data['title'];

        return $data;
    }, 10);
    app(HookManager::class)->addAction('nexus.entity.created', function ($model) use (&$createdFired) {
        $createdFired = true;
    }, 10);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo'])
        ->set('data.title', 'Creating hook check')
        ->call('save')
        ->assertHasNoErrors();

    expect($creatingFired)->toBeTrue()
        ->and($createdFired)->toBeTrue()
        ->and(Demo::where('title', 'HOOKED-Creating hook check')->exists())->toBeTrue();
});

it('fires nexus.entity.updating and nexus.entity.updated when a record is updated via ModuleForm', function () {
    $demo = Demo::create(['title' => 'Original']);

    $updatingFired = false;
    $updatedFired = false;

    app(HookManager::class)->addFilter('nexus.entity.updating', function (array $data) use (&$updatingFired) {
        $updatingFired = true;
        $data['title'] = 'HOOKED-'.$data['title'];

        return $data;
    }, 10);
    app(HookManager::class)->addAction('nexus.entity.updated', function ($model) use (&$updatedFired) {
        $updatedFired = true;
    }, 10);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo', 'id' => (string) $demo->id])
        ->set('data.title', 'Updated')
        ->call('save')
        ->assertHasNoErrors();

    expect($updatingFired)->toBeTrue()
        ->and($updatedFired)->toBeTrue()
        ->and($demo->fresh()->title)->toBe('HOOKED-Updated');
});

it('fires nexus.entity.deleting and nexus.entity.deleted when a record is deleted via ModuleTable', function () {
    $demo = Demo::create(['title' => 'To be deleted']);

    $deletingFired = false;
    $deletedFired = false;

    app(HookManager::class)->addAction('nexus.entity.deleting', function ($model) use (&$deletingFired) {
        $deletingFired = true;
    }, 10);
    app(HookManager::class)->addAction('nexus.entity.deleted', function ($model) use (&$deletedFired) {
        $deletedFired = true;
    }, 10);

    Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->call('runAction', 'delete', (string) $demo->id);

    expect($deletingFired)->toBeTrue()
        ->and($deletedFired)->toBeTrue()
        ->and(Demo::find($demo->id))->toBeNull();
});

it('fires nexus.entity.restoring and nexus.entity.restored for a group restore', function () {
    // WidgetPlacement (extends Nodex\Nexus\Models\WidgetInstance) is used
    // here specifically because it uses SoftDeletes — Demo doesn't, and
    // RestoreActionGroupMethod no-ops for a model that isn't soft-deletable.
    $placement = WidgetPlacement::create(['name' => 'Restore hook check', 'widget_key' => 'recentDemoRecords', 'is_enabled' => true]);
    $placement->delete();

    app()->instance('request', Request::create('/', 'POST', ['items' => [$placement->id]]));

    $restoringFired = false;
    $restoredFired = false;

    app(HookManager::class)->addAction('nexus.entity.restoring', function ($model) use (&$restoringFired) {
        $restoringFired = true;
    }, 10);
    app(HookManager::class)->addAction('nexus.entity.restored', function ($model) use (&$restoredFired) {
        $restoredFired = true;
    }, 10);

    $moduleConfig = ModuleManager::getModuleConfig('WidgetPlacement');
    RestoreActionGroupMethod::handle(app(DefaultGroupActionRequest::class), $moduleConfig);

    expect($restoringFired)->toBeTrue()
        ->and($restoredFired)->toBeTrue()
        ->and($placement->fresh()->trashed())->toBeFalse();
});

it('fires nexus.form.building when a module\'s form is assembled', function () {
    $fired = false;
    app(HookManager::class)->addAction('nexus.form.building', function ($config, string $moduleName) use (&$fired) {
        if ($moduleName === 'demo') {
            $fired = true;
        }
    }, 10);

    Livewire::test(ModuleForm::class, ['moduleName' => 'Demo']);

    expect($fired)->toBeTrue();
});

it('fires nexus.form.fields_prepared, mutating the fields the legacy FormBuilder path returns', function () {
    app(HookManager::class)->addFilter('nexus.form.fields_prepared', function (array $fields, string $moduleName) {
        if (isset($fields['title'])) {
            $fields['title']->label = 'HOOKED-'.$fields['title']->label;
        }

        return $fields;
    }, 10);

    $moduleConfig = ModuleManager::getModuleConfig('Demo');
    $built = FormBuilder::build($moduleConfig);

    expect($built['fields']['title']->label)->toStartWith('HOOKED-');
});

it('fires nexus.table.building and nexus.table.data_prepared when a module\'s table is assembled', function () {
    Demo::create(['title' => 'Table hook check']);

    $buildingFired = false;
    app(HookManager::class)->addAction('nexus.table.building', function ($config, string $moduleName) use (&$buildingFired) {
        if (strtolower($moduleName) === 'demo') {
            $buildingFired = true;
        }
    }, 10);
    app(HookManager::class)->addFilter('nexus.table.data_prepared', function ($data, string $moduleName) {
        return $data;
    }, 10);

    $module = Module::findByName('demo');
    $built = app(TableBuilder::class)->build($module, []);

    expect($buildingFired)->toBeTrue()
        ->and($built['data'])->not->toBeNull();
});

it('fires nexus.module.action_executed for a table action', function () {
    $demo = Demo::create(['title' => 'Action executed check']);

    $fired = [];
    app(HookManager::class)->addAction('nexus.module.action_executed', function (string $moduleName, string $actionName) use (&$fired) {
        $fired[] = $actionName;
    }, 10);

    Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->call('toggleBool', (string) $demo->id, 'is_active');

    expect($fired)->toContain('boolToggle');
});

it('fires nexus.validation.preparing before a dedicated Request validates', function () {
    $fired = false;
    app(HookManager::class)->addAction('nexus.validation.preparing', function ($request) use (&$fired) {
        $fired = true;
    }, 10);

    $this->post(
        route('nexus.module.action', ['module' => 'demo', 'action' => 'store']),
        ['title' => 'Preparing hook check'],
    );

    expect($fired)->toBeTrue();
});

it('fires nexus.module.installed and nexus.module.uninstalled', function () {
    $installedFired = false;
    $uninstalledFired = false;

    app(HookManager::class)->addAction('nexus.module.installed', function (string $name) use (&$installedFired) {
        if (strtolower($name) === 'demo') {
            $installedFired = true;
        }
    }, 10);
    app(HookManager::class)->addAction('nexus.module.uninstalled', function (string $name) use (&$uninstalledFired) {
        if (strtolower($name) === 'demo') {
            $uninstalledFired = true;
        }
    }, 10);

    ModuleManager::install('Demo');
    expect($installedFired)->toBeTrue();

    app(ModuleManager::class)->uninstall('Demo');
    expect($uninstalledFired)->toBeTrue();
});
