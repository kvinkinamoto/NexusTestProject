<?php

namespace App\Nexus\Modules\BonusTransaction\Enums;

/**
 * USED/CANCELLED are schema-complete but unreachable this iteration — there
 * is no spend-at-checkout flow or admin-cancel action yet, both explicitly
 * out of scope (matches ZentaraStartProject exactly).
 */
enum BonusTransactionStatusEnum: string
{
    case PENDING = 'pending';
    case AVAILABLE = 'available';
    case USED = 'used';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';
}
