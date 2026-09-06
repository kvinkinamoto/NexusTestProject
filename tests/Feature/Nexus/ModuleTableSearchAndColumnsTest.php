<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleTable;
use Spatie\Permission\Models\Permission;

/**
 * Two pieces of admin-table functionality that existed only as dead backend
 * code before this session: generic search, and per-user column visibility.
 *
 * Search: Nodex\Nexus\Filters\FilterHandler already implemented a generic
 * 'search' case (LIKE across every DB column) for any module with a
 * #[TableFilter(type: 'search')], but AddFilterActionMethod::handle() only
 * ever called a module's *own* Filters/ModuleFilterHandler — for any module
 * without one (Demo, until this session), $filterModuleHandler was null and
 * the whole filter loop was skipped, so the rendered search box accepted
 * input that silently did nothing. Fixed by falling back to `new
 * FilterHandler` there.
 *
 * Columns: TableBuilder::build() already read nexus_user_table_preferences.
 * visible_columns per (user, module) and filtered the rendered column list
 * against it — but nothing ever wrote to that column. ModuleTable::
 * toggleColumnVisibility() is the missing write path.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);
    $this->actingAs($this->admin);
});

it('filters Demo records by the generic search fallback with no per-module filter handler', function () {
    Demo::create(['title' => 'Findable Widget']);
    Demo::create(['title' => 'Something else entirely']);

    $rendered = Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->set('filter.search', 'Findable');

    $titles = collect($rendered->viewData('tableData')['data']->items())->pluck('title')->all();

    expect($titles)->toBe(['Findable Widget']);
});

it('shows every record again once the search filter is cleared', function () {
    Demo::create(['title' => 'Findable Widget']);
    Demo::create(['title' => 'Something else entirely']);

    $rendered = Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->set('filter.search', 'Findable')
        ->set('filter.search', '');

    expect($rendered->viewData('tableData')['data']->total())->toBe(2);
});

it('hides a column via the real Livewire toggle and persists the preference', function () {
    $rendered = Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->call('toggleColumnVisibility', 'count');

    $visible = collect($rendered->viewData('tableData')['columns'])->map(fn ($c) => $c->name)->all();
    expect($visible)->not->toContain('count');

    $stored = DB::table('nexus_user_table_preferences')
        ->where('user_id', $this->admin->id)
        ->where('module', 'Demo')
        ->value('visible_columns');

    expect(json_decode($stored, true))->not->toContain('count');
});

it('shows a hidden column again when toggled a second time', function () {
    $rendered = Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->call('toggleColumnVisibility', 'count')
        ->call('toggleColumnVisibility', 'count');

    $visible = collect($rendered->viewData('tableData')['columns'])->map(fn ($c) => $c->name)->all();
    expect($visible)->toContain('count');
});

it('refuses to hide the last remaining visible column', function () {
    $component = Livewire::test(ModuleTable::class, ['moduleName' => 'Demo']);
    $allNames = collect($component->viewData('tableData')['allColumns'])->map(fn ($c) => $c->name)->values()->all();

    foreach (array_slice($allNames, 1) as $name) {
        $component->call('toggleColumnVisibility', $name);
    }

    // Exactly one column left visible.
    $rendered = $component->call('toggleColumnVisibility', $allNames[0]);
    $visible = collect($rendered->viewData('tableData')['columns'])->map(fn ($c) => $c->name)->all();

    expect($visible)->toBe([$allNames[0]]);
});

it('scopes column preferences per user, not globally', function () {
    $otherAdmin = User::factory()->create();
    $otherAdmin->givePermissionTo(AdminPanelPermissionEnum::ALL->value);

    Livewire::test(ModuleTable::class, ['moduleName' => 'Demo'])
        ->call('toggleColumnVisibility', 'count');

    $this->actingAs($otherAdmin);
    $rendered = Livewire::test(ModuleTable::class, ['moduleName' => 'Demo']);
    $visible = collect($rendered->viewData('tableData')['columns'])->map(fn ($c) => $c->name)->all();

    expect($visible)->toContain('count');
});
