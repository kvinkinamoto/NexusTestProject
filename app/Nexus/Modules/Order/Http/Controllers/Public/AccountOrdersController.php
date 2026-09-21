<?php

namespace App\Nexus\Modules\Order\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\Order\Requests\UpdateAccountOrderRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class AccountOrdersController extends Controller
{
    public function index(): View
    {
        $orders = auth()->user()->orders()->with(['products', 'status'])->latest()->paginate(10);

        return view('order::public.account-orders', ['orders' => $orders]);
    }

    public function show(Order $order): View
    {
        $this->authorizeOwner($order);
        $order->load(['products.product', 'status']);

        return view('order::public.account-order-show', ['order' => $order]);
    }

    /**
     * Only the contact/comment fields are editable, never the order lines —
     * changing what was bought would mean re-running stock/pricing/promotion
     * resolution, which belongs to placing a new order, not editing one.
     * Blocked once payment_status is PAID for the same reason "Оплатити"
     * disappears then: a paid order is a settled financial record.
     */
    public function update(UpdateAccountOrderRequest $request, Order $order): RedirectResponse
    {
        $this->authorizeOwner($order);
        $this->authorizeEditable($order);

        $data = $request->validated();
        $data['needs_phone_call'] = $request->boolean('needs_phone_call');

        $order->update($data);

        return redirect()->route('account.orders.show', $order)->with('status', __('Замовлення оновлено.'));
    }

    public function destroy(Order $order): RedirectResponse
    {
        $this->authorizeOwner($order);
        $this->authorizeEditable($order);

        $order->delete();

        return redirect()->route('account.orders')->with('status', __('Замовлення видалено.'));
    }

    private function authorizeOwner(Order $order): void
    {
        if ($order->user_id !== auth()->id()) {
            abort(403);
        }
    }

    private function authorizeEditable(Order $order): void
    {
        if ($order->payment_status === PaymentStatus::PAID) {
            abort(403);
        }
    }
}
