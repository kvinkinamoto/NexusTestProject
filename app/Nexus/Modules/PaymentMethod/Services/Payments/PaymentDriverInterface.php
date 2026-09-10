<?php

namespace App\Nexus\Modules\PaymentMethod\Services\Payments;

use App\Nexus\Modules\Order\Models\Order;

interface PaymentDriverInterface
{
    public function createPayment(Order $order);

    public function handleCallback(array $data);

    public function checkPayment(Order $order);
}
