<?php

namespace App\Nexus\Modules\Order\Observers;

use App\Nexus\Modules\Order\Enums\PaymentStatus;
use App\Nexus\Modules\Order\Events\OrderPaid;
use App\Nexus\Modules\Order\Models\Order;
use App\Nexus\Modules\Order\Services\OrderService;
use App\Nexus\Modules\ShopProduct\Services\Actions\ReduceProductStockAction;
use App\Nexus\Modules\ShopProduct\Services\Actions\RestoreProductStockAction;

/**
 * Ported at full fidelity from ZentaraStartProject.
 */
class OrderObserver
{
    public function __construct(private readonly OrderService $orderService) {}

    public function created(Order $order): void
    {
        $order->history()->create([
            'status_id' => $order->status_id,
        ]);
    }

    public function updating(Order $order): void
    {
        if ($order->isDirty('status_id')) {
            $newSlug = $order->status()->value('slug');

            if ($newSlug === 'confirmed' && is_null($order->getOriginal('confirmed_at'))) {
                $order->confirmed_at = now();
            }

            if ($newSlug === 'delivered' && is_null($order->getOriginal('completed_at'))) {
                $order->completed_at = now();
            }
        }
    }

    public function updated(Order $order): void
    {
        if ($order->wasChanged('status_id')) {
            $newSlug = $order->status()->value('slug');

            $order->history()->create([
                'status_id' => $order->status_id,
            ]);

            if ($newSlug === 'confirmed' && is_null($order->getOriginal('confirmed_at'))) {
                $this->applyStock($order, ReduceProductStockAction::class);
            }

            if ($newSlug === 'cancelled' && ! is_null($order->getOriginal('confirmed_at'))) {
                $this->applyStock($order, RestoreProductStockAction::class);
            }
        }

        if ($order->wasChanged('completed_at') && $order->completed_at !== null) {
            $this->orderService->addOrderBonuses($order);
        }

        if ($order->wasChanged('payment_status') && $order->payment_status === PaymentStatus::PAID) {
            OrderPaid::dispatch($order);
        }
    }

    private function applyStock(Order $order, string $actionClass): void
    {
        $order->loadMissing('products.product');

        foreach ($order->products as $orderProduct) {
            $product = $orderProduct->product;
            if (! $product) {
                continue;
            }

            $actionClass::handle($product, (int) $orderProduct->quantity);
        }
    }
}
