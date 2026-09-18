<?php

use App\Models\User;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\OrderStatus\Models\OrderStatus;

beforeEach(function () {
    $this->artisan('nexus:permission:init')->run();
    $this->artisan('nexus:module:install', ['name' => 'ShopProduct'])->run();
    $this->artisan('nexus:module:install', ['name' => 'OrderStatus'])->run();
    $this->artisan('nexus:module:install', ['name' => 'Order'])->run();

    OrderStatus::create(['name' => 'New', 'slug' => 'new', 'is_default' => true, 'publish' => true]);
});

it('shows the redeem-gift form to a logged-in user', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('account.redeem-gift.show'))
        ->assertOk();
});

it('redirects guests to log in before they can see the redeem form', function () {
    $this->get(route('account.redeem-gift.show'))->assertRedirect(route('login'));
});

it('redeems a valid code and redirects to the orders page with a success message', function () {
    Order::create([
        'user_name' => 'Bob', 'user_phone' => '+1', 'is_gift' => true,
        'gift_code' => 'ABCDEFGHIJ', 'payment_status' => PaymentStatus::PAID,
        'products_price' => 100, 'total_price' => 100,
    ]);
    $recipient = User::factory()->create();

    $response = $this->actingAs($recipient)
        ->post(route('account.redeem-gift.store'), ['code' => 'abcdefghij']);

    $response->assertRedirect(route('account.orders'));
    $response->assertSessionHas('status');
    expect(Order::where('gift_code', 'ABCDEFGHIJ')->first()->recipient_user_id)->toBe($recipient->id);
});

it('shows an error for an invalid code instead of redeeming anything', function () {
    $response = $this->actingAs(User::factory()->create())
        ->from(route('account.redeem-gift.show'))
        ->post(route('account.redeem-gift.store'), ['code' => 'NOPE12345']);

    $response->assertRedirect(route('account.redeem-gift.show'));
    $response->assertSessionHasErrors('code');
});
