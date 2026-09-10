<?php

namespace App\Nexus\Modules\Bonus\Commands;

use App\Nexus\Modules\Bonus\Services\BonusService;
use Illuminate\Console\Command;

class ExpireAvailableBonusesCommand extends Command
{
    protected $signature = 'bonuses:expire';

    protected $description = 'Flip AVAILABLE bonus transactions to EXPIRED once their expires_at has passed.';

    public function handle(BonusService $bonusService): int
    {
        $count = $bonusService->expireAvailable();
        $this->info("Expired {$count} bonus transaction(s).");

        return self::SUCCESS;
    }
}
