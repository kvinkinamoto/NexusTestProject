<?php

namespace App\Nexus\Modules\DiscountRule\Enums;

enum ScopeTarget: string
{
    case ALL = 'all';
    case CATEGORY = 'category';
    case PRODUCTS = 'products';
}
