<?php

namespace App\Nexus\Modules\PaymentMethod\Services;

use App\Nexus\Modules\PaymentMethod\Models\PaymentMethod;
use Illuminate\Support\Collection;

class PaymentMethodService
{
    public function getPaymentMethods(): Collection
    {
        return PaymentMethod::query()
            ->publish()
            ->get()
            ->groupBy('type');
    }
}
