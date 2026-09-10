<?php

use App\Models\User;
use App\Nexus\Modules\Backup\Jobs\RunBackupJob;
use App\Nexus\Modules\Backup\Models\Backup;
use Illuminate\Support\Facades\Queue;
use Livewire\Livewire;
use Nodex\Nexus\Enums\AdminPanelPermissionEnum;
use Nodex\Nexus\Livewire\ModuleForm;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Backup'])->run();

    Permission::findOrCreate(AdminPanelPermissionEnum::ALL->value, 'web');
    Permission::findOrCreate(AdminPanelPermissionEnum::ADMIN_PANEL->value, 'web');

    $this->admin = User::factory()->create();
    $this->admin->givePermissionTo([AdminPanelPermissionEnum::ALL->value, AdminPanelPermissionEnum::ADMIN_PANEL->value]);
    $this->actingAs($this->admin);
});

it('lists backup configs on the index route', function () {
    Backup::create(['name' => 'Nightly']);

    $this->get('/admin/Backup/action/index')->assertOk();
});

it('creates a backup config through the generic admin CRUD', function () {
    Livewire::test(ModuleForm::class, ['moduleName' => 'Backup'])
        ->set('data.name', 'Weekly full')
        ->set('data.backup_database', true)
        ->set('data.backup_code', true)
        ->set('data.backup_storage', true)
        ->set('data.max_backups', 5)
        ->call('save')
        ->assertHasNoErrors();

    $backup = Backup::where('name', 'Weekly full')->firstOrFail();
    expect($backup->backup_database)->toBeTrue();
    expect($backup->backup_code)->toBeTrue();
    expect($backup->backup_storage)->toBeTrue();
    expect($backup->max_backups)->toBe(5);
});

it('queues a RunBackupJob when the run action is clicked', function () {
    Queue::fake();
    $backup = Backup::create(['name' => 'Nightly']);

    $this->post("/admin/Backup/action/run/{$backup->id}")->assertRedirect();

    Queue::assertPushed(RunBackupJob::class, fn (RunBackupJob $job) => $job->backupId === $backup->id);
});

it('404s the run action for a non-existent backup id', function () {
    $this->post('/admin/Backup/action/run/999999')->assertNotFound();
});
