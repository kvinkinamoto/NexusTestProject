<?php

namespace App\Nexus\Modules\License\Listeners;

use App\Nexus\Modules\License\Models\License;
use App\Nexus\Modules\License\Notifications\LicensesIssuedNotification;
use App\Nexus\Modules\Order\Events\OrderPaid;
use Illuminate\Database\Eloquent\Collection;

/**
 * One License row per unit of quantity on each paid order line — "quantity"
 * already means "number of project licenses" (see the product page's own
 * "Ліцензія на 1 проєкт" copy), so a line bought at quantity 3 issues 3
 * separate keys rather than one key with a higher activation limit.
 * Guest orders (no user_id) are skipped — there's no /account/licenses to
 * put them in without an account; same guard OrderService::addOrderBonuses()
 * already uses for the same reason. Idempotent: re-dispatch of the same
 * already-issued order is a no-op, guarding against OrderPaid ever firing
 * twice for one order. Emails the buyer once, only when licenses were
 * actually created this call — never on the guest-skip or already-issued
 * early returns above.
 */
class IssueLicensesOnOrderPaid
{
    public function handle(OrderPaid $event): void
    {
        $order = $event->order;

        if (! $order->user_id) {
            return;
        }

        if (License::query()->where('order_id', $order->id)->exists()) {
            return;
        }

        $order->loadMissing('products', 'user');

        $issued = new Collection;

        foreach ($order->products as $orderProduct) {
            for ($i = 0; $i < (int) $orderProduct->quantity; $i++) {
                $issued->push(License::create([
                    'user_id' => $order->user_id,
                    'order_id' => $order->id,
                    'product_id' => $orderProduct->product_id,
                    'license_key' => License::generateKey(),
                    'status' => 'active',
                    'max_activations' => 1,
                ]));
            }
        }

        if ($issued->isNotEmpty() && $order->user) {
            $order->user->notify(new LicensesIssuedNotification($order, $issued));
        }
    }
}
