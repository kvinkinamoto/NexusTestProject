<?php

use App\Nexus\Modules\Restore\Models\Restore;
use App\Nexus\Modules\Restore\Services\RestoreService;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Backup'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Restore'])->run();

    Storage::fake('local');
});

it('scans the configured disk and creates one row per archive, inferring its type from the filename', function () {
    Storage::disk('local')->put('backups/nightly_2026_01_01_000000_db.sql', 'dump');
    Storage::disk('local')->put('backups/nightly_2026_01_01_000000_code.zip', 'zip');
    Storage::disk('local')->put('backups/nightly_2026_01_01_000000_storage.zip', 'zip');
    Storage::disk('local')->put('backups/unrelated.txt', 'noise');

    app(RestoreService::class)->scanBackups();

    expect(Restore::count())->toBe(4);
    expect(Restore::where('filename', 'nightly_2026_01_01_000000_db.sql')->value('type'))->toBe('db');
    expect(Restore::where('filename', 'nightly_2026_01_01_000000_code.zip')->value('type'))->toBe('code');
    expect(Restore::where('filename', 'nightly_2026_01_01_000000_storage.zip')->value('type'))->toBe('storage');
    expect(Restore::where('filename', 'unrelated.txt')->value('type'))->toBe('unknown');
});

it('removes rows for archives that are no longer present on disk', function () {
    Storage::disk('local')->put('backups/keep_db.sql', 'dump');
    Storage::disk('local')->put('backups/gone_db.sql', 'dump');
    app(RestoreService::class)->scanBackups();
    expect(Restore::count())->toBe(2);

    Storage::disk('local')->delete('backups/gone_db.sql');
    app(RestoreService::class)->scanBackups();

    expect(Restore::count())->toBe(1);
    expect(Restore::first()->filename)->toBe('keep_db.sql');
});

it('marks a row failed and rethrows for an unrecognized backup type', function () {
    Storage::disk('local')->put('backups/mystery.dat', 'noise');
    app(RestoreService::class)->scanBackups();
    $restore = Restore::where('filename', 'mystery.dat')->firstOrFail();

    expect(fn () => app(RestoreService::class)->restore($restore))->toThrow(RuntimeException::class);

    $restore->refresh();
    expect($restore->status)->toBe('failed');
    expect($restore->last_error)->toContain('unknown backup type');
});

it('marks a db-type row failed when the database driver is unsupported', function () {
    // Same reasoning as BackupServiceTest's equivalent case: the test suite
    // runs on sqlite, which restoreDatabase() doesn't handle, so this is
    // reachable without faking config('database.default').
    Storage::disk('local')->put('backups/nightly_db.sql', 'dump');
    app(RestoreService::class)->scanBackups();
    $restore = Restore::where('filename', 'nightly_db.sql')->firstOrFail();

    expect(fn () => app(RestoreService::class)->restore($restore))->toThrow(RuntimeException::class);

    $restore->refresh();
    expect($restore->status)->toBe('failed');
    expect($restore->last_error)->toContain('Unsupported database driver');
});

it('rejects a PostgreSQL database restore as not implemented, matching the old project', function () {
    // Calls the private restoreDatabase() directly instead of going through
    // restore()'s full orchestration: that branch throws before touching
    // the database at all, but restore()'s catch block would otherwise call
    // markFailed()->save() while config('database.default') is still faked
    // to 'pgsql' — and this test suite's real connection is an in-memory
    // sqlite database, which doesn't survive being re-opened under another
    // connection name (a fresh :memory: handle is a distinct, empty
    // database), so that save() would fail with "no such table".
    $service = app(RestoreService::class);
    $realDriver = config('database.default');
    config(['database.default' => 'pgsql']);

    $method = new ReflectionMethod($service, 'restoreDatabase');
    $method->setAccessible(true);

    try {
        expect(fn () => $method->invoke($service, '/tmp/does-not-matter.sql'))
            ->toThrow(RuntimeException::class, 'PostgreSQL restore not fully implemented yet.');
    } finally {
        // Restore the real driver name before the test ends — Laravel's
        // RefreshDatabase rolls back a transaction on the "default"
        // connection at teardown, and a pgsql connection isn't actually
        // configured/reachable in this test environment.
        config(['database.default' => $realDriver]);
    }
});
