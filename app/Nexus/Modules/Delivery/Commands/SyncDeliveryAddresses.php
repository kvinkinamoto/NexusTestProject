<?php

namespace App\Nexus\Modules\Delivery\Commands;

use App\Nexus\Modules\Delivery\Services\DeliveryAddressSyncService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Deliberately doesn't hardcode a carrier list — it iterates whatever
 * DeliveryAddressSyncService::syncers() finds registered on the
 * 'delivery.address_syncers' hook (one #[Filter] per carrier plugin, see
 * App\Nexus\Plugins\{NovaPoshta,UkrPoshta}Delivery), so a carrier plugin
 * added or removed changes what this command syncs without editing it.
 */
class SyncDeliveryAddresses extends Command
{
    protected $signature = 'delivery:sync-addresses {carrier? : a registered carrier key (e.g. nova-poshta), omit to sync every registered carrier}';

    protected $description = 'Cache each registered carrier\'s cities and branches locally for the checkout address picker.';

    public function handle(DeliveryAddressSyncService $service): int
    {
        $requestedCarrier = $this->argument('carrier');
        $syncers = $service->syncers();

        if (empty($syncers)) {
            $this->warn('No delivery address syncers are registered — nothing to do.');

            return self::SUCCESS;
        }

        if ($requestedCarrier && ! isset($syncers[$requestedCarrier])) {
            $this->error("No address syncer registered for carrier: {$requestedCarrier}. Registered: ".implode(', ', array_keys($syncers)));

            return self::FAILURE;
        }

        $exitCode = self::SUCCESS;
        foreach ($syncers as $carrierKey => $syncer) {
            if ($requestedCarrier && $carrierKey !== $requestedCarrier) {
                continue;
            }

            $this->info("Syncing {$carrierKey} addresses...");
            try {
                $result = $syncer->sync(fn (string $stage, int $done, ?int $total) => $this->reportProgress($stage, $done, $total));
                $this->info("{$carrierKey}: {$result['cities']} cities, {$result['warehouses']} warehouses.");
            } catch (Throwable $e) {
                $this->error("{$carrierKey} sync failed: {$e->getMessage()}");
                Log::error("delivery:sync-addresses failed for {$carrierKey}", ['exception' => $e]);
                $exitCode = self::FAILURE;
            }
        }

        return $exitCode;
    }

    private function reportProgress(string $stage, int $done, ?int $total): void
    {
        $this->output->write("\r  {$stage}: {$done}".($total ? "/{$total}" : '').'   ');
        if ($total !== null && $done >= $total) {
            $this->output->writeln('');
        }
    }
}
