<?php

namespace App\Nexus\Modules\Bonus\Enums;

enum BonusTypeEnum: string
{
    case REGISTRATION = 'registration';
    case PURCHASE = 'purchase';
    case REVIEW = 'review';
    case MANUAL = 'manual';
}
