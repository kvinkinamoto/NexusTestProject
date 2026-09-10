<?php

use App\Models\User;
use App\Nexus\Modules\Bonus\Commands\ActivatePendingBonusesCommand;
use App\Nexus\Modules\Bonus\Commands\ExpireAvailableBonusesCommand;
use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use App\Nexus\Modules\BonusTransaction\Enums\BonusTransactionStatusEnum;
use App\Nexus\Modules\BonusTransaction\Models\BonusTransaction;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Bonus'])->run();

    $this->user = User::factory()->create();
});

/**
 * Module-provided commands aren't callable via $this->artisan() in Pest when
 * the module is installed mid-test — see
 * feedback_nexus_module_command_discovery_path memory. Drive the command's
 * own Symfony lifecycle directly instead.
 */
function runCommand(string $class): void
{
    $command = app($class);
    $command->setLaravel(app());
    $command->run(new ArrayInput([]), new NullOutput);
}

it('activates pending bonus transactions via the command', function () {
    BonusTransaction::create(['user_id' => $this->user->id, 'type' => BonusTypeEnum::MANUAL, 'amount' => 10, 'status' => BonusTransactionStatusEnum::PENDING, 'available_at' => now()->subMinute()]);

    runCommand(ActivatePendingBonusesCommand::class);

    expect(BonusTransaction::available()->count())->toBe(1);
});

it('expires available bonus transactions via the command', function () {
    BonusTransaction::create(['user_id' => $this->user->id, 'type' => BonusTypeEnum::MANUAL, 'amount' => 10, 'status' => BonusTransactionStatusEnum::AVAILABLE, 'available_at' => now(), 'expires_at' => now()->subMinute()]);

    runCommand(ExpireAvailableBonusesCommand::class);

    expect(BonusTransaction::query()->where('status', BonusTransactionStatusEnum::EXPIRED)->count())->toBe(1);
});
