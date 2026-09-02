<?php

namespace App\Nexus\Modules\User\Enums;

enum UserType: string
{
    case ADMIN = 'Admin';
    case CUSTOMER = 'Customer';

    public const DEFAULT = self::CUSTOMER;
}
