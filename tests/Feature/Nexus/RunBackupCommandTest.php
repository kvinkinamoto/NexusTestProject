<?php

use App\Nexus\Modules\Backup\Commands\RunBackupCommand;
use App\Nexus\Modules\Backup\Models\Backup;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Backup'])->run();

    Storage::fake('local');
});

/**
 * Auto-discovered module commands aren't callable via $this->artisan() when
 * the module was only just installed inside this same test's beforeEach —
 * console registration already happened at provider boot, before the
 * install call ran. Drive the command's own Symfony lifecycle directly
 * instead (see feedback_nexus_module_command_discovery_path memory).
 */
function runBackupCommand(array $arguments = []): int
{
    $command = app(RunBackupCommand::class);
    $command->setLaravel(app());

    return $command->run(new ArrayInput($arguments), new NullOutput);
}

it('runs every configured backup when no id is given', function () {
    Backup::create(['name' => 'First', 'backup_database' => false, 'backup_storage' => true]);
    Backup::create(['name' => 'Second', 'backup_database' => false, 'backup_storage' => true]);

    expect(runBackupCommand())->toBe(0);
    expect(Backup::where('last_status', 'success')->count())->toBe(2);
});

it('runs only the given backup id', function () {
    $target = Backup::create(['name' => 'First', 'backup_database' => false, 'backup_storage' => true]);
    $other = Backup::create(['name' => 'Second', 'backup_database' => false, 'backup_storage' => true]);

    expect(runBackupCommand(['id' => $target->id]))->toBe(0);

    expect($target->refresh()->last_status)->toBe('success');
    expect($other->refresh()->last_status)->toBeNull();
});

it('fails the command when a matching backup config does not exist', function () {
    expect(runBackupCommand(['id' => 999999]))->toBe(1);
});
