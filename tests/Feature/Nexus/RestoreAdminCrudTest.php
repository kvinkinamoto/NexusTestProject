<?php

use App\Models\User;
use App\Nexus\Modules\Restore\Jobs\RunRestoreJob;
use App\Nexus\Modules\Restore\Models\Restore;
use App\Nexus\Modules\Restore\Services\RestoreService;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Backup'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Restore'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);

    Storage::fake('local');
});

it('auto-scans and lists archives found on disk when the index route is opened', function () {
    Storage::disk('local')->put('backups/nightly_db.sql', 'dump');

    $this->get('/admin/Restore/action/index')->assertOk();

    expect(Restore::where('filename', 'nightly_db.sql')->exists())->toBeTrue();
});

it('re-syncs the list via the scan action', function () {
    Storage::disk('local')->put('backups/one_db.sql', 'dump');
    $this->post('/admin/Restore/action/scan')->assertRedirect();
    expect(Restore::count())->toBe(1);

    Storage::disk('local')->delete('backups/one_db.sql');
    Storage::disk('local')->put('backups/two_db.sql', 'dump');
    $this->post('/admin/Restore/action/scan')->assertRedirect();

    expect(Restore::count())->toBe(1);
    expect(Restore::first()->filename)->toBe('two_db.sql');
});

it('queues a RunRestoreJob when the runRestore action is clicked', function () {
    Queue::fake();
    Storage::disk('local')->put('backups/nightly_db.sql', 'dump');
    app(RestoreService::class)->scanBackups();
    $restore = Restore::firstOrFail();

    $this->post("/admin/Restore/action/runRestore/{$restore->id}")->assertRedirect();

    Queue::assertPushed(RunRestoreJob::class, fn (RunRestoreJob $job) => $job->restoreId === $restore->id);
});
