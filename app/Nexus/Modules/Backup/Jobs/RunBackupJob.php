<?php

namespace App\Nexus\Modules\Backup\Jobs;

use App\Nexus\Modules\Backup\Models\Backup;
use App\Nexus\Modules\Backup\Services\BackupService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunBackupJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(public readonly int $backupId) {}

    public function handle(BackupService $service): void
    {
        $backup = Backup::find($this->backupId);

        if (! $backup) {
            return;
        }

        try {
            $service->run($backup);
        } catch (\Throwable $e) {
            Log::warning('RunBackupJob failed', [
                'backup_id' => $this->backupId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
