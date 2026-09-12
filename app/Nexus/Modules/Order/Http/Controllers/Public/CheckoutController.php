<?php

namespace App\Nexus\Modules\Order\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Delivery\Models\Delivery;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function index(): View
    {
        return view('order::public.checkout', [
            'deliveries' => Delivery::query()->publish()->with('types')->get(),
            'paymentMethods' => PaymentMethod::query()->publish()->get(),
        ]);
    }

    /**
     * No server-side "thank you" redirect exists in the existing checkout
     * API (POST /api/orders returns pure JSON, never a redirect) — the order
     * id comes back from that JSON response and the storefront JS sends the
     * browser here itself. Looking the order up by id (no auth/ownership
     * check) mirrors a standard guest-checkout confirmation page.
     */
    public function thankYou(Request $request): View
    {
        $order = Order::query()
            ->with(['products', 'status', 'deliveryMethod', 'paymentMethod'])
            ->findOrFail($request->query('order'));

        return view('order::public.checkout-thankyou', ['order' => $order]);
    }
}
