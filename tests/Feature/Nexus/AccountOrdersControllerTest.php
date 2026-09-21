<?php

use App\Models\User;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;

/**
 * /account/orders previously had no way to get back to a still-unpaid
 * order — no link, no payment status shown, nothing. A buyer whose LiqPay
 * attempt failed or who closed the tab before paying had no way back in.
 */
beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

it('shows a pay link for a pending order with a gateway payment method', function () {
    $order = Order::create([
        'user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000',
        'total_price' => 500, 'payment_method' => 'liqpay', 'payment_status' => PaymentStatus::PENDING,
    ]);

    $this->get('/account/orders')
        ->assertOk()
        ->assertSee(route('payment.pay', $order->id));
});

it('does not show a pay link for an already-paid order', function () {
    $order = Order::create([
        'user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000',
        'total_price' => 500, 'payment_method' => 'liqpay', 'payment_status' => PaymentStatus::PAID,
    ]);

    $this->get('/account/orders')
        ->assertOk()
        ->assertDontSee(route('payment.pay', $order->id));
});

it('does not show a pay link for a cancelled order', function () {
    $order = Order::create([
        'user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000',
        'total_price' => 500, 'payment_method' => 'liqpay', 'payment_status' => PaymentStatus::CANCELLED,
    ]);

    $this->get('/account/orders')
        ->assertOk()
        ->assertDontSee(route('payment.pay', $order->id));
});

it('only lists the authenticated user\'s own orders', function () {
    $otherUser = User::factory()->create();
    Order::create(['user_id' => $otherUser->id, 'user_name' => 'Alice', 'user_phone' => '+380111111', 'total_price' => 100]);

    $this->get('/account/orders')->assertOk()->assertSee('У вас ще немає замовлень');
});

it('shows the order detail page with an editable contact form while unpaid', function () {
    $order = Order::create([
        'user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000',
        'total_price' => 500, 'payment_method' => 'liqpay', 'payment_status' => PaymentStatus::PENDING,
    ]);

    $this->get(route('account.orders.show', $order))
        ->assertOk()
        ->assertSee('name="user_name"', false)
        ->assertSee('Видалити замовлення');
});

it('shows read-only contact details and no delete button once paid', function () {
    $order = Order::create([
        'user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000',
        'total_price' => 500, 'payment_method' => 'liqpay', 'payment_status' => PaymentStatus::PAID,
    ]);

    $this->get(route('account.orders.show', $order))
        ->assertOk()
        ->assertDontSee('name="user_name"', false)
        ->assertDontSee('Видалити замовлення');
});

it('403s viewing another user\'s order', function () {
    $otherUser = User::factory()->create();
    $order = Order::create(['user_id' => $otherUser->id, 'user_name' => 'Alice', 'user_phone' => '+380111111', 'total_price' => 100]);

    $this->get(route('account.orders.show', $order))->assertForbidden();
});

it('updates contact fields on an unpaid order', function () {
    $order = Order::create([
        'user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000',
        'total_price' => 500, 'payment_method' => 'liqpay', 'payment_status' => PaymentStatus::PENDING,
    ]);

    $this->put(route('account.orders.update', $order), [
        'user_name' => 'Robert', 'user_phone' => '+380999999', 'user_comment' => 'Please call after 6pm',
    ])->assertRedirect(route('account.orders.show', $order));

    expect($order->fresh()->user_name)->toBe('Robert');
    expect($order->fresh()->user_comment)->toBe('Please call after 6pm');
});

it('refuses to update an already-paid order', function () {
    $order = Order::create([
        'user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000',
        'total_price' => 500, 'payment_method' => 'liqpay', 'payment_status' => PaymentStatus::PAID,
    ]);

    $this->put(route('account.orders.update', $order), ['user_name' => 'Robert', 'user_phone' => '+380999999'])
        ->assertForbidden();

    expect($order->fresh()->user_name)->toBe('Bob');
});

it('deletes an unpaid order', function () {
    $order = Order::create([
        'user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000',
        'total_price' => 500, 'payment_method' => 'liqpay', 'payment_status' => PaymentStatus::PENDING,
    ]);

    $this->delete(route('account.orders.destroy', $order))->assertRedirect(route('account.orders'));

    expect(Order::find($order->id))->toBeNull();
});

it('refuses to delete an already-paid order', function () {
    $order = Order::create([
        'user_id' => $this->user->id, 'user_name' => 'Bob', 'user_phone' => '+380000000',
        'total_price' => 500, 'payment_method' => 'liqpay', 'payment_status' => PaymentStatus::PAID,
    ]);

    $this->delete(route('account.orders.destroy', $order))->assertForbidden();

    expect(Order::find($order->id))->not->toBeNull();
});

it('403s deleting another user\'s order', function () {
    $otherUser = User::factory()->create();
    $order = Order::create(['user_id' => $otherUser->id, 'user_name' => 'Alice', 'user_phone' => '+380111111', 'total_price' => 100]);

    $this->delete(route('account.orders.destroy', $order))->assertForbidden();
    expect(Order::find($order->id))->not->toBeNull();
});
