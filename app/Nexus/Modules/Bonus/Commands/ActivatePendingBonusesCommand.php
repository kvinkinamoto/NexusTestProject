<?php

namespace App\Nexus\Modules\Bonus\Commands;

use App\Nexus\Modules\Bonus\Services\BonusService;
use Illuminate\Console\Command;

class ActivatePendingBonusesCommand extends Command
{
    protected $signature = 'bonuses:activate';

    protected $description = 'Flip PENDING bonus transactions to AVAILABLE once their available_at has passed.';

    public function handle(BonusService $bonusService): int
    {
        $count = $bonusService->activatePending();
        $this->info("Activated {$count} bonus transaction(s).");

        return self::SUCCESS;
    }
}
