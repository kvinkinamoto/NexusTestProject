<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Nodex\Nexus\Dto\Widgets\WidgetContext;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Enums\WidgetSurface;
use Nodex\Nexus\Events\ApiResourceBuilding;
use Nodex\Nexus\Events\ExportRowBuilding;
use Nodex\Nexus\Events\GatheringValidationRules;
use Nodex\Nexus\Events\GlobalSearchCompleted;
use Nodex\Nexus\Events\ImportRowBuilding;
use Nodex\Nexus\Events\PermissionChecking;
use Nodex\Nexus\Events\SidebarMenuBuilding;
use Nodex\Nexus\Events\WidgetOutputResolving;
use Nodex\Nexus\Http\Actions\CheckUserPermissionAction;
use Nodex\Nexus\Http\Resources\NexusResource;
use Nodex\Nexus\Models\Module;
use Nodex\Nexus\Modules\Export\Jobs\MasterExportJob;
use Nodex\Nexus\Services\GlobalSearchService;
use Nodex\Nexus\Services\ModuleManager;
use Nodex\Nexus\Services\ModuleServiceForAdminPanel;
use Nodex\Nexus\Services\Validation\NexusRuleCollector;
use Nodex\Nexus\Services\Widgets\AdminDashboardRenderer;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for the Laravel-native event fired alongside each
 * nexus_filter()/nexus_action() plugin hook (see PluginHookCoverageTest.php
 * for the filter half of each pair) — deliberately overlapping extension
 * mechanisms by design: a module's own Listeners/ folder can react to these
 * without writing a #[TargetModule] plugin class at all.
 *
 * FieldTypesRegistering isn't covered here — it fires once during
 * NexusServiceProvider::boot(), before any test's own Event::listen() call
 * could possibly be registered in time (the same structural boot-timing gap
 * documented for module-scoped widget discovery elsewhere in this suite).
 * PluginSystemTest.php proves its paired nexus.field_types.register action
 * fires, via ExamplePlugin's #[Action] — HookManager's listener registration
 * happens earlier in the boot lifecycle (during register()) than a test's
 * own Event::listen() ever could.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
});

it('fires GatheringValidationRules for a module with no dedicated Request', function () {
    $fired = false;
    Event::listen(GatheringValidationRules::class, function (GatheringValidationRules $event) use (&$fired) {
        $fired = true;
        expect($event->moduleConfig->name)->toBe('demo')
            ->and($event->action)->toBe('store')
            ->and($event->rules)->toBeArray();
    });

    app(NexusRuleCollector::class)
        ->collect(ModuleManager::getModuleConfig('Demo'), 'store');

    expect($fired)->toBeTrue();
});

it('fires GatheringValidationRules for a module with a dedicated Request, mutable by reference', function () {
    Event::listen(GatheringValidationRules::class, function (GatheringValidationRules $event) {
        if ($event->moduleConfig->name === 'demo' && isset($event->rules['title'])) {
            $titleRules = is_string($event->rules['title']) ? explode('|', $event->rules['title']) : $event->rules['title'];
            $titleRules[] = 'max:5';
            $event->rules['title'] = $titleRules;
        }
    });

    $this->actingAs($this->admin);
    $response = $this->post(
        route('nexus.module.action', ['module' => 'demo', 'action' => 'store']),
        ['title' => 'Way too long for max:5'],
    );

    $response->assertSessionHasErrors('title');
});

it('fires WidgetOutputResolving for the admin dashboard html output, mutable by reference', function () {
    Event::listen(WidgetOutputResolving::class, function (WidgetOutputResolving $event) {
        if ($event->widgetKey === 'recentDemoRecords' && $event->kind === 'html') {
            $event->output .= '<!--EVENT-HTML-MARK-->';
        }
    });

    Demo::create(['title' => 'Event widget check']);

    $context = new WidgetContext(surface: WidgetSurface::Admin, user: $this->admin);
    $cards = app(AdminDashboardRenderer::class)->render(['recentDemoRecords'], $context);

    expect($cards[0]['html'])->toContain('EVENT-HTML-MARK');
});

it('fires ApiResourceBuilding for the generic NexusResource fallback, mutable by reference', function () {
    Event::listen(ApiResourceBuilding::class, function (ApiResourceBuilding $event) {
        if ($event->resource instanceof Demo) {
            $event->data['eventMarked'] = true;
        }
    });

    $demo = Demo::create(['title' => 'Event resource check']);
    $data = NexusResource::make($demo)->resolve();

    expect($data)->toHaveKey('eventMarked', true);
});

it('fires GlobalSearchCompleted after every module has contributed, mutable by reference', function () {
    Event::listen(GlobalSearchCompleted::class, function (GlobalSearchCompleted $event) {
        $event->results[] = ['module' => 'event-source', 'label' => 'Event Source', 'items' => []];
    });

    $this->actingAs($this->admin);
    $results = app(GlobalSearchService::class)->search('anything');

    expect(collect($results)->pluck('module'))->toContain('event-source');
});

it('fires ExportRowBuilding for every exported row, mutable by reference', function () {
    Storage::fake('local');

    Event::listen(ExportRowBuilding::class, function (ExportRowBuilding $event) {
        $event->row = array_map(fn ($cell) => $cell === '' ? $cell : "EVT-{$cell}", $event->row);
    });

    Demo::create(['title' => 'Export event check']);

    $cacheKey = 'export-event-test';
    (new MasterExportJob('demo', $cacheKey, [], null))->handle();

    $progress = Cache::get($cacheKey);
    $csv = Storage::disk('local')->get($progress['filePath']);

    expect($csv)->toContain('EVT-Export event check');
});

it('fires ImportRowBuilding for every imported row, mutable by reference', function () {
    Event::listen(ImportRowBuilding::class, function (ImportRowBuilding $event) {
        $event->data['title'] = 'EVT-'.($event->data['title'] ?? '');
    });

    $file = UploadedFile::fake()->createWithContent('demo-import-event.csv', "Title\nEvent Row\n");

    $this->actingAs($this->admin);
    $response = $this->post(
        route('nexus.module.action', ['module' => 'demo', 'action' => 'import']),
        ['file' => $file],
    );

    $response->assertOk()->assertJsonPath('created', 1);
    expect(Demo::where('title', 'EVT-Event Row')->exists())->toBeTrue();
});

it('fires PermissionChecking with the resolved decision, mutable by reference', function () {
    $noPermsUser = User::factory()->create();
    $module = Module::findByName('demo');

    Event::listen(PermissionChecking::class, function (PermissionChecking $event) {
        if ($event->action === 'index') {
            $event->result = true;
        }
    });

    $this->actingAs($noPermsUser);
    expect(CheckUserPermissionAction::handle('index', $module))->toBeTrue();
});

it('fires SidebarMenuBuilding before the sidebar menu filter, mutable by reference', function () {
    Event::listen(SidebarMenuBuilding::class, function (SidebarMenuBuilding $event) {
        $event->menu[] = 'EVENT-MENU-MARK';
    });

    $this->actingAs($this->admin);
    $menu = app(ModuleServiceForAdminPanel::class)->getSideBarMenu();

    expect($menu)->toContain('EVENT-MENU-MARK');
});
