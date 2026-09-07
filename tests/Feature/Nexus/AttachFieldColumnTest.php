<?php

use App\Models\User;
use App\Nexus\Modules\ConfirmFixture\Models\ConfirmFixture;
use App\Nexus\Modules\ConfirmFixtureNote\Models\ConfirmFixtureNote;
use App\Nexus\Plugins\ConfirmFixtureNote\ConfirmFixtureNotePlugin;
use Livewire\Livewire;
use Nodex\Nexus\Dto\ModuleDtos\DefaultModuleConfigurationDto;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Enums\RelationConfigParamsEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Nodex\Nexus\Livewire\ModuleTable;
use Nodex\Nexus\Services\HookManager;
use Nodex\Nexus\Services\ModuleManager;
use Nodex\Nexus\Services\PluginManager;
use Spatie\Permission\Models\Permission;

/**
 * Proves #[AttachField]/#[AttachColumn]/#[AttachFilter]
 * (packages/nodex/nexus/src/Attributes) — declarative sugar over
 * #[TargetModule]'s handle() for attaching an admin field/column/filter
 * onto a module a plugin doesn't own, paired with the pre-existing
 * #[AttachRelation]/#[AttachScope] for the data layer. Fixture:
 * ConfirmFixtureNote (the "specialized" module) attaches a `notes`
 * relation-manager field, a `notes_count` column, a `notes_search` filter,
 * and the underlying `notes`/`notes_count` Eloquent relation+scope onto
 * ConfirmFixture (the "base" module) — see both models' docblocks for the
 * full story.
 *
 * Also regression-covers the relationManager.blade.php fix this required:
 * method_exists($model, $field->name) can never see a relation attached via
 * Model::resolveRelationUsing(), so it always silently showed "no related
 * records" for any #[AttachRelation]-backed field before this fix.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();

    // Authenticate BEFORE installing: ModuleManager caches a module's
    // resolved config per (singleton) instance — installing ConfirmFixture
    // while unauthenticated (console-style, no viewer) would permanently
    // poison that cache with every #[AttachField(permission:)]-gated entry
    // evaluated as "denied", since PluginManager::apply() reads the *current*
    // auth()->user() at resolve time. A real deployment never hits this — a
    // fresh HTTP/console process gets its own ModuleManager instance — but a
    // single Pest test reuses one container across "install" and "assert",
    // so order here has to mirror "an already-logged-in admin installs the
    // module", not "installed via an unauthenticated console command, then
    // someone logs in".
    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    $admin = User::factory()->create();
    $admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    $this->actingAs($admin);

    $this->artisan('nexus:module:install', ['name' => 'ConfirmFixture'])->run();
});

it('attaches the field, relation and column onto ConfirmFixture\'s resolved config', function () {
    $this->artisan('nexus:module:install', ['name' => 'ConfirmFixtureNote'])->run();

    $config = ModuleManager::getModuleConfig('ConfirmFixture');

    expect($config->form->fields)->toHaveKey('notes');
    expect($config->form->fields['notes']->type)->toBe('relationManager');
    expect($config->form->fields['notes']->label)->toBe('confirmFixtureNote::translate.notes');

    expect($config->relations->is_available)->toHaveKey('notes');
    expect($config->relations->is_available['notes']->relatedModule)->toBe('confirmfixturenote');
    expect($config->relations->is_available['notes']->type)->toBe(RelationConfigParamsEnum::HAS_MANY->value);

    expect($config->table->columns)->toHaveKey('notes_count');

    expect($config->table->filters)->toHaveKey('notes_search');
    expect($config->table->filters['notes_search']->type)->toBe('search');
});

it('actually resolves the dynamically attached relation and computed count', function () {
    $this->artisan('nexus:module:install', ['name' => 'ConfirmFixtureNote'])->run();

    $fixture = ConfirmFixture::create(['name' => 'Fixture with notes']);
    ConfirmFixtureNote::create(['confirm_fixture_id' => $fixture->id, 'body' => 'First note']);
    ConfirmFixtureNote::create(['confirm_fixture_id' => $fixture->id, 'body' => 'Second note']);

    $fresh = ConfirmFixture::query()->find($fixture->id);

    // Method-call syntax, not the $model->notes dynamic-property shortcut —
    // Model::resolveRelationUsing() (what #[AttachRelation] is backed by)
    // only ever supports the former, a documented Eloquent limitation.
    expect($fresh->notes()->get())->toHaveCount(2);
    expect($fresh->notes_count)->toBe(2);
});

it('renders the relation-manager field and count column through the real admin UI', function () {
    $this->artisan('nexus:module:install', ['name' => 'ConfirmFixtureNote'])->run();

    $fixture = ConfirmFixture::create(['name' => 'Fixture with notes']);
    ConfirmFixtureNote::create(['confirm_fixture_id' => $fixture->id, 'body' => 'First note']);

    Livewire::test(ModuleTable::class, ['moduleName' => 'ConfirmFixture'])
        ->assertOk()
        ->assertSee('Notes');

    Livewire::test(ModuleForm::class, ['moduleName' => 'ConfirmFixture', 'id' => (string) $fixture->id])
        ->assertOk()
        ->assertSee('First note');
});

it('disappears from a freshly-built config with no error when the plugin is disabled', function () {
    $this->artisan('nexus:module:install', ['name' => 'ConfirmFixtureNote'])->run();

    config(['nexus.plugins.disabled' => [ConfirmFixtureNotePlugin::class]]);

    // A fresh PluginManager instance, built after the config change, so its
    // own discovery (unlike the container-bound singleton ModuleManager::
    // getModuleConfig() would reuse) actually respects the disabled plugin —
    // same technique as PluginSystemTest's "skips a plugin listed in
    // config(nexus.plugins.disabled)" case.
    $manager = new PluginManager(app(HookManager::class));
    $manager->autoDiscover();

    $config = $manager->apply('ConfirmFixture', new DefaultModuleConfigurationDto);

    expect($config->form->fields)->not->toHaveKey('notes');
    expect($config->table->columns)->not->toHaveKey('notes_count');
    expect($config->table->filters)->not->toHaveKey('notes_search');
    expect($config->relations->is_available)->not->toHaveKey('notes');
});

it('hides a permission-gated attachment from a viewer without that permission, while an ungated one stays visible', function () {
    $this->artisan('nexus:module:install', ['name' => 'ConfirmFixtureNote'])->run();

    // A real (non-super-admin) authenticated viewer — deliberately no
    // 'confirmfixturenote_view' grant, proving #[AttachField(permission:)]/
    // #[AttachColumn(permission:)] actually gate per-viewer rather than
    // just per-plugin-enabled-or-not (see the "disabled" test above for
    // that other axis). Goes through PluginManager::apply() directly on a
    // bare DTO, the same technique the "disabled" test above uses — NOT
    // ModuleManager::getModuleConfig(), which caches its resolved DTO on
    // its own singleton and would otherwise still hand back whichever
    // viewer's result got resolved (and cached) first this test, since a
    // single Pest test — unlike a real request — can switch actingAs()
    // more than once inside one already-booted container.
    $viewer = User::factory()->create();
    $this->actingAs($viewer);

    $pluginManager = app(PluginManager::class);
    $config = $pluginManager->apply('ConfirmFixture', new DefaultModuleConfigurationDto);

    expect($config->form->fields)->not->toHaveKey('notes');
    expect($config->table->columns)->not->toHaveKey('notes_count');
    expect($config->relations->is_available)->not->toHaveKey('notes');

    // notes_search carries no `permission` — the default, backward-compatible
    // "always visible" behavior must be unaffected by the gated fields above.
    expect($config->table->filters)->toHaveKey('notes_search');
});

it('shows a permission-gated attachment once the viewer is granted that permission', function () {
    $this->artisan('nexus:module:install', ['name' => 'ConfirmFixtureNote'])->run();

    Permission::findOrCreate('confirmfixturenote_view', 'web');
    $viewer = User::factory()->create();
    $viewer->givePermissionTo('confirmfixturenote_view');
    $this->actingAs($viewer);

    $pluginManager = app(PluginManager::class);
    $config = $pluginManager->apply('ConfirmFixture', new DefaultModuleConfigurationDto);

    expect($config->form->fields)->toHaveKey('notes');
    expect($config->table->columns)->toHaveKey('notes_count');
    expect($config->relations->is_available)->toHaveKey('notes');
});

it('keeps rendering ConfirmFixture without error when the module owning the relation was never installed but the field-attaching plugin still runs', function () {
    // Deliberately skips installing ConfirmFixtureNote (simulates it having
    // been removed/never enabled): its Relations/ folder is never scanned,
    // so #[AttachRelation]/#[AttachScope] never register `notes`/
    // `notes_count` on ConfirmFixture. ConfirmFixtureNotePlugin still runs
    // regardless — plugin discovery only depends on its own plugin folder,
    // not on any Nexus module's install/enabled state — so ConfirmFixture's
    // config still ends up with an orphaned "notes" field pointing at a
    // relation that was never wired. Must fail quiet, not 500.
    $fixture = ConfirmFixture::create(['name' => 'Orphaned fixture']);

    Livewire::test(ModuleTable::class, ['moduleName' => 'ConfirmFixture'])->assertOk();

    Livewire::test(ModuleForm::class, ['moduleName' => 'ConfirmFixture', 'id' => (string) $fixture->id])
        ->assertOk()
        ->assertSee(__('nexus::translate.no_related_records'));
});
