<?php

use App\Models\User;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Models\Module;
use Nodex\Nexus\Services\ModuleManager;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for the case-sensitive module-name bug found while
 * building Import/Export: nexus:module:install stores the `nexus_modules`
 * row's `name` using whatever casing the CLI/registry call happened to use
 * (typically the module's StudlyCase folder name, e.g. 'Demo'), but
 * #[Module(name:)] — and so every route() call built from a module config's
 * ->name — is always the lowercase canonical slug (e.g. 'demo'). MySQL's
 * default collation made `WHERE name = 'demo'` match a 'Demo' row anyway,
 * masking the bug in production; SQLite (this suite, and the CI matrix)
 * compares case-sensitively, so the same lookup genuinely failed there
 * (confirmed via tests/Feature/Nexus/ImportExportTest.php, which had to use
 * 'Demo' rather than 'demo' before this fix — see Module::findByName()'s
 * docblock for the full story).
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    // Deliberately capitalized, matching the folder name — same as the CLI
    // and every other test in this suite. The row this creates is what
    // findByName()/resolveRouteBinding() have to see past.
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('finds a module by name regardless of casing', function () {
    expect(Module::findByName('demo'))->not->toBeNull()
        ->and(Module::findByName('DEMO'))->not->toBeNull()
        ->and(Module::findByName('Demo'))->not->toBeNull()
        ->and(Module::findByName('dEmO')->id)->toBe(Module::findByName('Demo')->id);
});

it('resolves a {module} route parameter using the lowercase #[Module(name:)] slug, not just the DB row\'s stored casing', function () {
    // The DB row was created as 'Demo' (see beforeEach); this hits the
    // route with the canonical lowercase slug instead — exactly what
    // route('nexus.module.action', ['module' => $moduleConfig->name, ...])
    // builds everywhere in the package's own Blade views.
    $response = $this->get(route('nexus.module.action', ['module' => 'demo', 'action' => 'index']));

    $response->assertOk();
});

it('does not create a duplicate row when install() runs again under different casing', function () {
    expect(Module::query()->where('name', 'like', '%emo')->count())->toBe(1);

    ModuleManager::install('demo');

    expect(Module::query()->where('name', 'like', '%emo')->count())->toBe(1);
});
