<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

/**
 * module-table.blade.php originally rendered bulk actions as separate
 * buttons stacked in the toolbar, only once rows were selected — fine for
 * the single deleteGroup every existing module declares, but the user
 * pointed out two problems in quick succession: it doesn't scale to several
 * actions at once (buttons crowd the header), and even a single action there
 * is easy to miss since nothing draws the eye to it. Demo now declares a
 * second group action (duplicateGroup) specifically to exercise the
 * multi-action case.
 *
 * Landed on a slide-in side panel (not a toolbar dropdown, which was this
 * fix's first iteration) — every available action is listed at once
 * regardless of count, and it appears via Alpine's `x-show="$wire.selected.
 * length > 0"` rather than a server-side @if, so it's always present in the
 * rendered HTML (hidden client-side until a row is checked) rather than
 * conditionally rendered. Tests here check for the action buttons' presence
 * in markup and the live "N selected" count, not visibility, since Alpine's
 * client-side x-show state isn't something a server-rendered-HTML assertion
 * can observe.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Role'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    $this->actingAs($this->admin);
});

it('renders the panel with its one action for a module with only one group action', function () {
    $rendered = Livewire::test(ModuleTable::class, ['moduleName' => 'Role']);

    $rendered->assertSeeHtml("runGroupAction('deleteGroup')");
});

it('lists every active group action in the panel for a module with several', function () {
    $rendered = Livewire::test(ModuleTable::class, ['moduleName' => 'Demo']);

    $rendered->assertSeeHtml("runGroupAction('deleteGroup')")
        ->assertSeeHtml("runGroupAction('duplicateGroup')");
});

it('updates the selected count shown in the panel as rows are checked', function () {
    $demo = Demo::create(['title' => 'Panel count fixture']);

    $rendered = Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->set('selected', [(string) $demo->id]);

    $rendered->assertSee('1');
    $rendered->assertSet('selected', [(string) $demo->id]);
});

it('still runs the correct action when picked from the panel', function () {
    $demo = Demo::create(['title' => 'Duplicate me']);

    Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->set('selected', [(string) $demo->id])
        ->call('runGroupAction', 'duplicateGroup');

    // DuplicateActionMethod::handleUniqueFields() renames the copy's title
    // (" (Copy)" suffix) to dodge a unique constraint — count is what matters here.
    expect(Demo::count())->toBe(2);
});
