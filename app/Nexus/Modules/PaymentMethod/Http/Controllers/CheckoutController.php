<?php

namespace App\Nexus\Modules\PaymentMethod\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Services\Payments\PaymentManager;

class CheckoutController extends Controller
{
    public function pay(int $orderId)
    {
        $query = Order::with(['products', 'paymentMethod']);

        if (auth()->check()) {
            $query->where('user_id', auth()->id());
        } else {
            $query->whereNull('user_id');
        }

        $order = $query->findOrFail($orderId);

        // A free order is already marked PAID by OrderService and never
        // needed a gateway in the first place — reaching this URL for one
        // (stale link, back button, a manual guess) must not try to charge
        // it again.
        if ($order->payment_status === PaymentStatus::PAID) {
            return redirect()->route('shop.checkout.thankyou', ['order' => $order->id]);
        }

        $driver = app(PaymentManager::class)->driver($order->payment_method);

        return $driver->createPayment($order);
    }
}
