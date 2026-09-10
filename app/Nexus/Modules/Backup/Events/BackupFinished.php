<?php

namespace App\Nexus\Modules\Backup\Events;

use App\Nexus\Modules\Backup\Models\Backup;
use Illuminate\Foundation\Events\Dispatchable;

class BackupFinished
{
    use Dispatchable;

    public function __construct(
        public readonly Backup $backup,
        public readonly string $type,
        public readonly string $filename,
    ) {}
}
