<?php

use App\Nexus\Modules\Backup\Models\Backup;
use App\Nexus\Modules\Backup\Services\BackupService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Backup'])->run();

    Storage::fake('local');
});

it('zips storage/app/public and uploads it to the configured disk under backups/', function () {
    Storage::disk('public')->put('avatars/a.jpg', 'fake-image-bytes');

    $backup = Backup::create([
        'name' => 'Storage only',
        'backup_database' => false,
        'backup_code' => false,
        'backup_storage' => true,
        'max_backups' => 10,
    ]);

    app(BackupService::class)->run($backup);

    $backup->refresh();
    expect($backup->last_status)->toBe('success');
    expect($backup->last_error)->toBeNull();
    expect($backup->last_run_at)->not->toBeNull();

    $files = collect(Storage::disk('local')->files('backups'))
        ->filter(fn (string $file) => str_ends_with($file, '_storage.zip'));

    expect($files)->toHaveCount(1);
});

it('deletes the oldest archives beyond max_backups after a run', function () {
    $backup = Backup::create([
        'name' => 'Cleanup test',
        'backup_database' => false,
        'backup_code' => false,
        'backup_storage' => true,
        'max_backups' => 2,
    ]);

    foreach (range(1, 3) as $i) {
        Storage::disk('local')->put("backups/cleanup-test_202{$i}_01_01_000000_storage.zip", 'old-archive');
        touch(Storage::disk('local')->path("backups/cleanup-test_202{$i}_01_01_000000_storage.zip"), now()->subDays(10 - $i)->timestamp);
    }

    app(BackupService::class)->run($backup);

    $files = Storage::disk('local')->files('backups');

    // 3 pre-seeded + 1 just created = 4, cleaned up down to max_backups (2).
    expect($files)->toHaveCount(2);
});

it('marks the config failed with an error message when the database driver is unsupported', function () {
    // The test suite itself runs on sqlite (see phpunit.xml) — not in
    // BackupService's supported list (mysql/mariadb/pgsql) — so this
    // exercises the real branch without faking config('database.default')
    // and risking Eloquent's own save() calls inside run() landing on an
    // unmigrated connection.
    $backup = Backup::create([
        'name' => 'DB only',
        'backup_database' => true,
        'backup_code' => false,
        'backup_storage' => false,
        'max_backups' => 10,
    ]);

    expect(fn () => app(BackupService::class)->run($backup))->toThrow(RuntimeException::class);

    $backup->refresh();
    expect($backup->last_status)->toBe('failed');
    expect($backup->last_error)->toContain('Unsupported database driver');
});
