<?php

use App\Models\User;
use App\Nexus\Modules\Bonus\Enums\BonusTypeEnum;
use App\Nexus\Modules\Bonus\Models\BonusRule;
use App\Nexus\Modules\Bonus\Services\BonusService;
use App\Nexus\Modules\BonusTransaction\Enums\BonusTransactionStatusEnum;
use App\Nexus\Modules\BonusTransaction\Models\BonusTransaction;
use App\Nexus\Modules\Order\Models\Order;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'Bonus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    $this->service = app(BonusService::class);
    $this->user = User::factory()->create();
});

it('returns null when no active rule exists for the type', function () {
    expect($this->service->awardRegistration($this->user))->toBeNull();
});

it('awards registration bonus immediately when delay_days is zero', function () {
    BonusRule::create(['type' => BonusTypeEnum::REGISTRATION, 'amount' => 100, 'delay_days' => 0, 'validity_days' => 180, 'is_active' => true]);

    $transaction = $this->service->awardRegistration($this->user);

    expect($transaction)->not->toBeNull();
    expect((float) $transaction->amount)->toBe(100.0);
    expect($transaction->status)->toBe(BonusTransactionStatusEnum::AVAILABLE);
    expect((int) $transaction->available_at->diffInDays($transaction->expires_at))->toBe(180);
});

it('awards a pending bonus when delay_days is set', function () {
    BonusRule::create(['type' => BonusTypeEnum::REGISTRATION, 'amount' => 100, 'delay_days' => 15, 'is_active' => true]);

    $transaction = $this->service->awardRegistration($this->user);

    expect($transaction->status)->toBe(BonusTransactionStatusEnum::PENDING);
    expect($transaction->available_at->isAfter(now()->addDays(14)))->toBeTrue();
});

it('ignores the rule amount for purchase bonuses, using the caller-supplied sum instead', function () {
    BonusRule::create(['type' => BonusTypeEnum::PURCHASE, 'amount' => 999, 'delay_days' => 0, 'is_active' => true]);
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '1']);

    $transaction = $this->service->awardPurchase($this->user, 42.5, $order);

    expect((float) $transaction->amount)->toBe(42.5);
    expect($transaction->source_id)->toBe($order->id);
    expect($transaction->source_type)->toBe($order->getMorphClass());
});

it('does not award a purchase bonus for a zero or negative amount', function () {
    BonusRule::create(['type' => BonusTypeEnum::PURCHASE, 'amount' => 999, 'is_active' => true]);
    $order = Order::create(['user_name' => 'Bob', 'user_phone' => '1']);

    expect($this->service->awardPurchase($this->user, 0, $order))->toBeNull();
});

it('awards a manual bonus with a custom description', function () {
    BonusRule::create(['type' => BonusTypeEnum::MANUAL, 'amount' => 0, 'is_active' => true]);

    $transaction = $this->service->awardManual($this->user, 25, 'Support goodwill credit');

    expect((float) $transaction->amount)->toBe(25.0);
    expect($transaction->description)->toBe('Support goodwill credit');
});

it('activates pending transactions once available_at has passed', function () {
    $rule = BonusRule::create(['type' => BonusTypeEnum::MANUAL, 'amount' => 0, 'is_active' => true]);
    BonusTransaction::create(['user_id' => $this->user->id, 'type' => BonusTypeEnum::MANUAL, 'amount' => 10, 'status' => BonusTransactionStatusEnum::PENDING, 'available_at' => now()->subMinute()]);
    BonusTransaction::create(['user_id' => $this->user->id, 'type' => BonusTypeEnum::MANUAL, 'amount' => 10, 'status' => BonusTransactionStatusEnum::PENDING, 'available_at' => now()->addDay()]);

    $count = $this->service->activatePending();

    expect($count)->toBe(1);
    expect(BonusTransaction::available()->count())->toBe(1);
});

it('expires available transactions once expires_at has passed', function () {
    BonusTransaction::create(['user_id' => $this->user->id, 'type' => BonusTypeEnum::MANUAL, 'amount' => 10, 'status' => BonusTransactionStatusEnum::AVAILABLE, 'available_at' => now(), 'expires_at' => now()->subMinute()]);
    BonusTransaction::create(['user_id' => $this->user->id, 'type' => BonusTypeEnum::MANUAL, 'amount' => 10, 'status' => BonusTransactionStatusEnum::AVAILABLE, 'available_at' => now(), 'expires_at' => null]);

    $count = $this->service->expireAvailable();

    expect($count)->toBe(1);
    expect(BonusTransaction::available()->count())->toBe(1);
});
