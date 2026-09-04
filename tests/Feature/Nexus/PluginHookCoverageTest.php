<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Nodex\Nexus\Dto\Widgets\WidgetContext;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Enums\WidgetSurface;
use Nodex\Nexus\Http\Actions\CheckUserPermissionAction;
use Nodex\Nexus\Http\Resources\NexusResource;
use Nodex\Nexus\Models\Module;
use Nodex\Nexus\Modules\Export\Jobs\MasterExportJob;
use Nodex\Nexus\Services\Actions\Admin\ImportActionMethod;
use Nodex\Nexus\Services\GlobalSearchService;
use Nodex\Nexus\Services\HookManager;
use Nodex\Nexus\Services\ModuleServiceForAdminPanel;
use Nodex\Nexus\Services\Widgets\AdminDashboardRenderer;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for every plugin extension point added in this pass —
 * an audit found NexusResource::toArray(), GlobalSearchService::search(),
 * widget rendering outside the API surface, MasterExportJob/ImportActionMethod's
 * row loops, CheckUserPermissionAction, and the sidebar menu builder all had
 * no way for a plugin to hook in at all. Each test here registers a
 * throwaway filter directly via HookManager (not through ExamplePlugin,
 * which is globally auto-discovered and active for every test in the suite
 * — a filter added there would run everywhere, not just here) and proves
 * the call site actually invokes it with the right arguments.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
});

it('lets a plugin filter a widget\'s HTML output on the admin dashboard via widget.html.{key}', function () {
    app(HookManager::class)->addFilter('widget.html.recentDemoRecords', function (string $html) {
        return $html.'<!--PLUGIN-HTML-MARK-->';
    }, 10);

    Demo::create(['title' => 'Dashboard widget check']);

    $context = new WidgetContext(surface: WidgetSurface::Admin, user: $this->admin);
    $cards = app(AdminDashboardRenderer::class)->render(['recentDemoRecords'], $context);

    expect($cards[0]['html'])->toContain('PLUGIN-HTML-MARK');
});

it('lets a plugin filter a widget\'s raw data via widget.data.{key} for the /api/widgets/{key} surface', function () {
    app(HookManager::class)->addFilter('widget.data.usersCount', function (array $data) {
        $data['pluginMarked'] = true;

        return $data;
    }, 10);

    $this->actingAs($this->admin);
    $response = $this->getJson(route('api.nexus.widgets.show', ['key' => 'usersCount']));

    $response->assertOk()->assertJsonPath('data.data.pluginMarked', true);
});

it('lets a plugin reshape a module\'s generic API resource output via nexus.api.resource', function () {
    app(HookManager::class)->addFilter('nexus.api.resource', function (array $data, $resource) {
        if ($resource instanceof Demo) {
            $data['pluginMarked'] = true;
        }

        return $data;
    }, 10);

    $demo = Demo::create(['title' => 'API resource check', 'api_only_note' => 'note']);

    $data = NexusResource::make($demo)->resolve();

    expect($data)->toHaveKey('pluginMarked', true)
        ->and($data)->toHaveKey('api_only_note', 'note');
});

it('lets a plugin append its own group of results via nexus.search.results', function () {
    app(HookManager::class)->addFilter('nexus.search.results', function (array $results, string $term) {
        $results[] = ['module' => 'external', 'label' => 'External', 'items' => [['id' => 'x', 'label' => "Found: {$term}", 'url' => '#']]];

        return $results;
    }, 10);

    $this->actingAs($this->admin);
    $results = app(GlobalSearchService::class)->search('anything');

    expect(collect($results)->pluck('module'))->toContain('external');
});

it('lets a plugin transform a row before it is written to the export CSV via nexus.export.row', function () {
    Storage::fake('local');

    app(HookManager::class)->addFilter('nexus.export.row', function (array $row, $model, string $moduleName) {
        return array_map(fn ($cell) => $cell === '' ? $cell : "FILTERED-{$cell}", $row);
    }, 10);

    Demo::create(['title' => 'Export row check']);

    $cacheKey = 'export-hook-test';
    (new MasterExportJob('demo', $cacheKey, [], null))->handle();

    $progress = Cache::get($cacheKey);
    expect($progress['status'])->toBe('completed');

    $csv = Storage::disk('local')->get($progress['filePath']);
    expect($csv)->toContain('FILTERED-Export row check');
});

it('lets a plugin transform a row before it is saved during CSV import via nexus.import.row', function () {
    app(HookManager::class)->addFilter('nexus.import.row', function (array $data, $moduleConfig) {
        $data['title'] = 'FILTERED-'.($data['title'] ?? '');

        return $data;
    }, 10);

    $file = UploadedFile::fake()->createWithContent('demo-import.csv', "Title\nOriginal Row\n");

    $this->actingAs($this->admin);
    $response = $this->post(
        route('nexus.module.action', ['module' => 'demo', 'action' => 'import']),
        ['file' => $file],
    );

    $response->assertOk()->assertJsonPath('created', 1);
    expect(Demo::where('title', 'FILTERED-Original Row')->exists())->toBeTrue();
});

it('lets a plugin override a permission decision via nexus.permission.check', function () {
    $noPermsUser = User::factory()->create();
    $module = Module::findByName('demo');

    expect(CheckUserPermissionAction::handle('index', $module))->toBeFalse();

    app(HookManager::class)->addFilter('nexus.permission.check', function (bool $result, string $action, $module, string $place) {
        return $action === 'index' ? true : $result;
    }, 10);

    $this->actingAs($noPermsUser);
    expect(CheckUserPermissionAction::handle('index', $module))->toBeTrue();
});

it('lets a plugin append a top-level sidebar menu entry via nexus.menu.sidebar', function () {
    app(HookManager::class)->addFilter('nexus.menu.sidebar', function (array $menu) {
        $menu[] = 'PLUGIN-MENU-MARK';

        return $menu;
    }, 10);

    $this->actingAs($this->admin);
    $menu = app(ModuleServiceForAdminPanel::class)->getSideBarMenu();

    expect($menu)->toContain('PLUGIN-MENU-MARK');
});
