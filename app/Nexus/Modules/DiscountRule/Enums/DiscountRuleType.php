<?php

namespace App\Nexus\Modules\DiscountRule\Enums;

enum DiscountRuleType: string
{
    case PRODUCT_DISCOUNT = 'product_discount';
    case QUANTITY_DISCOUNT = 'quantity_discount';
    case BUY_X_GET_Y = 'buy_x_get_y';
    case CART_THRESHOLD = 'cart_threshold';
    case FREE_SHIPPING = 'free_shipping';
    case BUNDLE = 'bundle';
    case FLASH_SALE = 'flash_sale';

    /**
     * Types resolved per cart line (product + quantity in isolation).
     */
    public function isLineLevel(): bool
    {
        return in_array($this, [self::PRODUCT_DISCOUNT, self::QUANTITY_DISCOUNT, self::BUY_X_GET_Y, self::FLASH_SALE], true);
    }

    /**
     * Types resolved once for the whole cart.
     */
    public function isCartLevel(): bool
    {
        return in_array($this, [self::CART_THRESHOLD, self::BUNDLE, self::FREE_SHIPPING], true);
    }
}
