<?php

namespace App\Nexus\Modules\DiscountRule\Enums;

enum ValueType: string
{
    case PERCENT = 'percent';
    case FIXED_AMOUNT = 'fixed_amount';
}
