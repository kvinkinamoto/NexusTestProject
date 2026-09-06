<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleSettingsForm;
use Nodex\Nexus\Services\SettingsBuilder;
use Spatie\Permission\Models\Permission;

/**
 * The #[Setting] registry (see project-nexus-settings-registry memory) also
 * works on a module that — unlike Settings itself — has a real bound model
 * and its own CRUD table: Demo declares items_per_page/
 * show_archived_by_default alongside its normal fields, to prove the two
 * mechanisms don't interfere with each other.
 *
 * The 'settings' action name is deliberately distinct from 'edit': caught
 * live (not by a test, until this file) that ModuleSettingsForm::save()
 * originally redirected to action=>'edit', which for a model-bound module
 * means "edit this one row" — with no id, NexusController::edit() +
 * ModuleForm::mount() silently reinterpreted that as the *create* action
 * instead of erroring, so saving Demo's settings redirected straight into a
 * blank "new Demo record" wizard. Fixed by redirecting to action=>'settings'
 * instead (see NexusController::settings()) — this test pins that down so
 * it can't quietly come back.
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

it('shows a Settings button on a module list page that declares #[Setting]s', function () {
    // 'Demo', matching the exact casing nexus:module:install stored the row
    // under — Module::findByName()'s docblock explains why this can't be
    // assumed lowercase (route()'s generated URL is a literal string build,
    // not a case-insensitive DB lookup like findByName() does).
    $response = $this->get(route('nexus.module.action', ['module' => 'Demo', 'action' => 'index']));

    $response->assertOk();
    $response->assertSee(route('nexus.module.action', ['module' => 'Demo', 'action' => 'settings']), false);
});

it('saves a model-bound module\'s settings without disturbing its own records', function () {
    $demo = Demo::create(['title' => 'Untouched Record']);

    Livewire::test(ModuleSettingsForm::class, ['moduleName' => 'demo'])
        ->set('data.items_per_page', 25)
        ->set('data.show_archived_by_default', true)
        ->call('save');

    expect(SettingsBuilder::get('demo', 'items_per_page', null))->toBe(25)
        ->and(SettingsBuilder::get('demo', 'show_archived_by_default', null))->toBeTrue()
        ->and(Demo::count())->toBe(1)
        ->and(Demo::first()->title)->toBe('Untouched Record');
});

it('redirects after saving settings back to the settings screen, never into the create form', function () {
    Livewire::test(ModuleSettingsForm::class, ['moduleName' => 'demo'])
        ->set('data.items_per_page', 25)
        ->call('save')
        ->assertRedirect(route('nexus.module.action', ['module' => 'demo', 'action' => 'settings']));

    expect(Demo::count())->toBe(0);
});

it('still edits an existing Demo record normally through the real edit action', function () {
    $demo = Demo::create(['title' => 'Original title']);

    Livewire::test(ModuleForm::class, ['moduleName' => 'demo', 'id' => (string) $demo->id])
        ->set('data.title', 'Updated title')
        ->call('save');

    expect($demo->fresh()->title)->toBe('Updated title')
        ->and(Demo::count())->toBe(1);
});
