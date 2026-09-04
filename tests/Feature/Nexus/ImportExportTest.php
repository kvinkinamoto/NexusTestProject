<?php

use App\Models\User;
use App\Nexus\Modules\Demo\Models\Demo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Modules\Export\Jobs\MasterExportJob;
use Spatie\Permission\Models\Permission;

/**
 * Regression coverage for the "Import/Export was never actually reachable"
 * fix: NexusController::import()/export()/progressExport()/downloadExport()
 * all existed already but had no routes at all, and export additionally
 * threw immediately (MasterExportJob didn't exist) — see the route
 * definitions in routes/web.php and the job class for the full story.
 */
beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Demo'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    // NexusAdminMiddleware (gates the whole /admin/* prefix) checks
    // ADMIN_PANEL directly via hasPermissionTo() — it doesn't go through
    // CheckUserPermissionAction's "ALL grants everything" shortcut the way
    // every other permission check in this codebase does. Every other test
    // in this suite uses Livewire::test(), which never touches HTTP routing
    // middleware at all, so this gap only shows up for a real HTTP request
    // like the ones this file makes.
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);

    Storage::fake('local');
});

it('imports a CSV through the (previously unrouted) import endpoint', function () {
    $csv = "Title\nImported Row One\nImported Row Two\n";
    $file = UploadedFile::fake()->createWithContent('demo.csv', $csv);

    $response = $this->post(route('nexus.module.import', ['module' => 'Demo']), ['file' => $file]);

    $response->assertOk()->assertJson(['created' => 2, 'updated' => 0, 'skipped' => 0]);
    expect(Demo::where('title', 'Imported Row One')->exists())->toBeTrue();
});

it('runs MasterExportJob end-to-end and produces a downloadable CSV of the module\'s records', function () {
    Demo::create(['title' => 'Exportable Row One']);
    Demo::create(['title' => 'Exportable Row Two']);

    $cacheKey = 'export_test_key';
    (new MasterExportJob('Demo', $cacheKey, [], $this->admin->id))->handle();

    $progress = Cache::get($cacheKey);
    expect($progress['status'])->toBe('completed')
        ->and($progress['processed'])->toBe(2)
        ->and($progress['filePath'])->not->toBeNull();

    Storage::disk('local')->assertExists($progress['filePath']);
    $content = Storage::disk('local')->get($progress['filePath']);
    expect($content)->toContain('Exportable Row One')
        ->toContain('Exportable Row Two');
});

it('scopes the export with a dynamic (UniversalFilterBuilder) filter the same way the table view would', function () {
    Demo::create(['title' => 'Draft Row', 'status' => 'draft']);
    Demo::create(['title' => 'Published Row', 'status' => 'published']);

    $cacheKey = 'export_lens_key';
    (new MasterExportJob('Demo', $cacheKey, ['dyn' => [['column' => 'status', 'operator' => '=', 'value' => 'published']]], $this->admin->id))->handle();

    $progress = Cache::get($cacheKey);
    expect($progress['status'])->toBe('completed')
        ->and($progress['processed'])->toBe(1);

    $content = Storage::disk('local')->get($progress['filePath']);
    expect($content)->toContain('Published Row')->not->toContain('Draft Row');
});

it('serves the completed export file through the download endpoint', function () {
    Demo::create(['title' => 'Downloadable Row']);

    $cacheKey = 'export_download_key';
    (new MasterExportJob('Demo', $cacheKey, [], $this->admin->id))->handle();
    $filePath = Cache::get($cacheKey)['filePath'];

    $response = $this->get(route('nexus.module.export.download', ['module' => 'Demo', 'filePath' => $filePath]));

    $response->assertOk();
});
