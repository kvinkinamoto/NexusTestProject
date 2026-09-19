<?php

namespace App\Nexus\Modules\PaymentMethod\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Order\Models\Order;
use Illuminate\Http\RedirectResponse;

class PaymentResultController extends Controller
{
    /**
     * LiqPay's `result_url` — the browser is redirected here right after
     * the buyer finishes on LiqPay's hosted page, independently of (and not
     * necessarily after) the async `server_url` callback that actually
     * flips `payment_status` to PAID. This previously ignored $orderId
     * entirely and always claimed success on a page with no flash-message
     * UI, regardless of whether payment actually succeeded — reusing the
     * real thank-you page here means the buyer sees the order's true
     * payment_status (PENDING/PAID/CANCELLED) instead of a silent no-op.
     */
    public function handle(int $orderId): RedirectResponse
    {
        Order::query()->findOrFail($orderId);

        return redirect()->route('shop.checkout.thankyou', ['order' => $orderId]);
    }
}
