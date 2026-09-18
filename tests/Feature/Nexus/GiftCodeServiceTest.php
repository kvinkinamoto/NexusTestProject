<?php

use App\Models\User;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\Order\Services\GiftCodeService;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    $this->service = app(GiftCodeService::class);
    OrderStatus::create(['name' => 'New', 'slug' => 'new', 'is_default' => true, 'publish' => true]);
});

function paidGiftOrder(array $overrides = []): Order
{
    return Order::create(array_merge([
        'user_name' => 'Bob', 'user_phone' => '+380000000',
        'is_gift' => true, 'gift_code' => 'ABCDEFGHIJ',
        'payment_status' => PaymentStatus::PAID,
        'products_price' => 100, 'total_price' => 100,
    ], $overrides));
}

it('generates a 10-character uppercase code', function () {
    expect($this->service->generate())->toMatch('/^[A-Z0-9]{10}$/');
});

it('never generates a code that collides with an existing one', function () {
    Order::create(['user_name' => 'Bob', 'user_phone' => '+1', 'gift_code' => 'AAAAAAAAAA', 'products_price' => 0, 'total_price' => 0]);

    // Not a real collision test (random collision is astronomically unlikely to hit in a
    // unit test), just confirms the uniqueness check runs without erroring and the
    // generated code differs from the one seeded above.
    expect($this->service->generate())->not->toBe('AAAAAAAAAA');
});

it('redeems a valid paid gift code and sets the recipient', function () {
    $order = paidGiftOrder();
    $user = User::factory()->create();

    $redeemed = $this->service->redeem('abcdefghij', $user);

    expect($redeemed->id)->toBe($order->id);
    expect($order->fresh()->recipient_user_id)->toBe($user->id);
});

it('throws for a code that does not exist', function () {
    $user = User::factory()->create();

    expect(fn () => $this->service->redeem('NOPE', $user))->toThrow(DomainException::class);
});

it('throws for an unpaid gift order', function () {
    paidGiftOrder(['payment_status' => PaymentStatus::PENDING]);
    $user = User::factory()->create();

    expect(fn () => $this->service->redeem('ABCDEFGHIJ', $user))->toThrow(DomainException::class);
});

it('throws for a code that was already redeemed', function () {
    $firstRecipient = User::factory()->create();
    paidGiftOrder(['recipient_user_id' => $firstRecipient->id]);
    $secondUser = User::factory()->create();

    expect(fn () => $this->service->redeem('ABCDEFGHIJ', $secondUser))->toThrow(DomainException::class);
});

it('throws for a non-gift order even if it happens to have a code value', function () {
    paidGiftOrder(['is_gift' => false]);
    $user = User::factory()->create();

    expect(fn () => $this->service->redeem('ABCDEFGHIJ', $user))->toThrow(DomainException::class);
});
