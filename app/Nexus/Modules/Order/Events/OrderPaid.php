<?php

namespace App\Nexus\Modules\Order\Events;

use App\Nexus\Modules\Order\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Fired once by OrderObserver::updated() the moment payment_status first
 * becomes PAID, regardless of which PaymentDriverInterface (LiqPay,
 * Monobank installments, Privat installments) made that happen — none of
 * them need to know about this event themselves. No listener reacts to it
 * yet; a later module (e.g. License, issuing a license key per order line)
 * attaches one via its own Listeners/ folder, auto-discovered by name-typed
 * handle(OrderPaid $event) — see the nexus-modules skill's "Reacting to
 * another module's lifecycle" section.
 */
class OrderPaid
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Order $order) {}
}
