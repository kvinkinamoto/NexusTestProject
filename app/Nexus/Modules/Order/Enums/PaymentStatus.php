<?php

namespace App\Nexus\Modules\Order\Enums;

enum PaymentStatus: int
{
    case PENDING = 1;
    case PAID = 2;
    case CANCELLED = 3;

    public function label(): string
    {
        return trans('order::translate.enum_payment_statuses.'.$this->name);
    }
}
