<?php

namespace App\Nexus\Modules\Backup\Commands;

use App\Nexus\Modules\Backup\Models\Backup;
use App\Nexus\Modules\Backup\Services\BackupService;
use Illuminate\Console\Command;

/**
 * Renamed from the old project's `zentara:backup:run` (single implicit
 * config row) to accept an optional {id} — this module supports several
 * named backup configs (see Backup model docblock), so a bare run must know
 * which one(s) to execute. Runs synchronously, not via RunBackupJob: this
 * command already runs out-of-band (an external cron calling artisan), so
 * there is no HTTP request to avoid blocking.
 */
class RunBackupCommand extends Command
{
    protected $signature = 'backup:run {id? : Run only this Backup config ID; runs every row otherwise}';

    protected $description = 'Run one or all configured backups';

    public function handle(BackupService $service): int
    {
        $backups = $this->argument('id')
            ? Backup::query()->where('id', $this->argument('id'))->get()
            : Backup::query()->get();

        if ($backups->isEmpty()) {
            $this->error('No matching backup configuration found.');

            return self::FAILURE;
        }

        $failed = false;

        foreach ($backups as $backup) {
            try {
                $service->run($backup);
                $this->info("Backup '{$backup->name}' finished successfully.");
            } catch (\Throwable $e) {
                $failed = true;
                $this->error("Backup '{$backup->name}' failed: {$e->getMessage()}");
            }
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
