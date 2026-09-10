<?php

namespace App\Nexus\Modules\Restore\Jobs;

use App\Nexus\Modules\Restore\Models\Restore;
use App\Nexus\Modules\Restore\Services\RestoreService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RunRestoreJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 3600;

    public function __construct(public readonly int $restoreId) {}

    public function handle(RestoreService $service): void
    {
        $restore = Restore::find($this->restoreId);

        if (! $restore) {
            return;
        }

        try {
            $service->restore($restore);
        } catch (\Throwable $e) {
            Log::warning('RunRestoreJob failed', [
                'restore_id' => $this->restoreId,
                'message' => $e->getMessage(),
            ]);
        }
    }
}
