<?php

namespace App\Nexus\Modules\PaymentMethod\Http\Controllers;

use App\Http\Controllers\Controller;
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
        $driver = app(PaymentManager::class)->driver($order->payment_method);

        return $driver->createPayment($order);
    }
}
